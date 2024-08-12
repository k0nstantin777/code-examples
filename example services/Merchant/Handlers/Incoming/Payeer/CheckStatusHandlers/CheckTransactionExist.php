<?php

namespace App\Services\Merchant\Handlers\Incoming\Payeer\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;
use App\Services\Payeer\Enums\TransactionDirection;
use App\Services\Payeer\Enums\TransactionType;
use App\Services\Payeer\RequestDTOs\HistoryRequestDTO;
use App\Services\Payeer\ValueObjects\Transaction;
use App\Services\Payeer\Exceptions\PayeerApiException;
use App\Services\Payeer\PayeerService;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        private PayeerService $payeerService
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $transactions = $this->findAppropriateTransactions($exchangeRequest);

        if (count($transactions) === 0) {
            return false;
        }

        $transaction = $this->getAppropriateTransaction($transactions, $exchangeRequest);

        $checkStatusRequest->set(
            CheckStatusRequestProperty::TXID,
            (string) (optional($transaction)->getId() ?? TransactionIdPlaceholder::NOT_FOUND),
        );

        if (null === $transaction) {
            return false;
        }

        $checkStatusRequest->set(CheckStatusRequestProperty::TRANSACTION, $transaction);
        return true;
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return Transaction[]
     */
    private function findAppropriateTransactions(ExchangeRequest $exchangeRequest) : array
    {
        try {
            $historyRequestDto = new HistoryRequestDTO();
            $historyRequestDto->setType(TransactionDirection::INCOMING);
            $historyRequestDto->setFrom($exchangeRequest->created_at);
            $historyRequestDto->setTo($exchangeRequest->created_at->addDay());

            $transactionHistory = $this->payeerService->getHistory($historyRequestDto)->getTransactions();

            if ($transactionHistory === null) {
                return [];
            }

            $transactions = [];
            foreach ($transactionHistory as $transaction) {
                if ($transaction->getType() === TransactionType::TRANSFER &&
                    $transaction->getCurrency() === $exchangeRequest->givenCurrency->code &&
                    $transaction->getSourceAccount()->getUsername() === $exchangeRequest->given_requisites
                ) {
                    $transactions[] = $transaction;
                }
            }

            return $transactions;
        } catch (PayeerApiException) {
            return [];
        }
    }

    /**
     * @param Transaction[] $transactions
     */
    private function getAppropriateTransaction(
        array $transactions,
        ExchangeRequest $exchangeRequest
    ) : ?Transaction {
        $exchangeRequestFilter = ExchangeRequestFilter::getInstance();

        foreach ($transactions as $transaction) {
            $duplicateExchangeRequest = $exchangeRequestFilter
                ->refresh()
                ->setPaymentTransactionId($transaction->getId())
                ->setGivenMerchantId($exchangeRequest->given_merchant_id)
                ->first();

            if (null === $duplicateExchangeRequest) {
                return $transaction;
            }
        }

        return null;
    }
}
