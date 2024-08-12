<?php

namespace App\Services\Merchant\Handlers\Incoming\PerfectMoney\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;
use App\Services\PerfectMoney\RequestDTOs\HistoryRequestDto;
use App\Services\PerfectMoney\Exceptions\PerfectMoneyApiException;
use App\Services\PerfectMoney\PerfectMoneyService;
use App\Services\PerfectMoney\ValueObjects\Transaction;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        private PerfectMoneyService $perfectMoneyService
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
            optional($transaction)->getBatch() ?? TransactionIdPlaceholder::NOT_FOUND,
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
            $historyRequestDto = new HistoryRequestDto();
            $historyRequestDto->setIsIncoming(true);
            $historyRequestDto->setIsOutgoing(false);
            $historyRequestDto->setAccount($exchangeRequest->given_requisites);
            $historyRequestDto->setFrom($exchangeRequest->created_at);
            $historyRequestDto->setTo($exchangeRequest->created_at->addDay());

            $transactionHistory = $this->perfectMoneyService->getHistory($historyRequestDto);

            if ($transactionHistory === null) {
                return [];
            }

            $transactions = [];
            foreach ($transactionHistory as $transaction) {
                if ($transaction->getPayerAccount() === $exchangeRequest->given_requisites
                ) {
                    $transactions[] = $transaction;
                }
            }

            return $transactions;
        } catch (PerfectMoneyApiException) {
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
                ->setPaymentTransactionId($transaction->getBatch())
                ->setGivenMerchantId($exchangeRequest->given_merchant_id)
                ->first();

            if (null === $duplicateExchangeRequest) {
                return $transaction;
            }
        }

        return null;
    }
}
