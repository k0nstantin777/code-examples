<?php

namespace App\Services\Merchant\Handlers\Incoming\AdvancedCash\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\AdvancedCash\AdvancedCashService;
use App\Services\AdvancedCash\Enums\TransactionDirection;
use App\Services\AdvancedCash\Enums\TransactionName;
use App\Services\AdvancedCash\Exceptions\AdvancedCashApiException;
use App\Services\AdvancedCash\RequestDtos\HistoryRequestDto;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;
use outcomingTransactionDTO;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        private AdvancedCashService $advancedCashService
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
            $transaction->id ?? TransactionIdPlaceholder::NOT_FOUND
        );

        if (null === $transaction) {
            return false;
        }

        $checkStatusRequest->set(CheckStatusRequestProperty::TRANSACTION, $transaction);
        return true;
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return outcomingTransactionDTO[]
     */
    private function findAppropriateTransactions(ExchangeRequest $exchangeRequest) : array
    {
        try {
            $wallet = preg_replace('/\s+/', '', $exchangeRequest->givenCurrency->payment_requisites);
            $createdAtToETC = $exchangeRequest->created_at->setTimezone('UTC');

            $requestDto = new HistoryRequestDto();
            $requestDto->setTransactionDirection(TransactionDirection::INCOMING);
            $requestDto->setWalletId($wallet);
            $requestDto->setTransactionName(TransactionName::INNER_SYSTEM);
            $requestDto->setStartTimeFrom($createdAtToETC->toDateTimeLocalString());
            $requestDto->setStartTimeTo($createdAtToETC->addDay()->toDateTimeLocalString());

            $transactionHistory = $this->advancedCashService->getHistory($requestDto)->return;

            if ($transactionHistory === null) {
                return [];
            }

            $transactions = [];
            foreach ($transactionHistory as $outcomingTransactionDTO) {
                if ($outcomingTransactionDTO->walletSrcId === $exchangeRequest->given_requisites) {
                    $transactions[] = $outcomingTransactionDTO;
                }
            }

            return $transactions;
        } catch (AdvancedCashApiException) {
            return [];
        }
    }

    /**
     * @param outcomingTransactionDTO[] $transactions
     */
    private function getAppropriateTransaction(
        array $transactions,
        ExchangeRequest $exchangeRequest
    ) : ?outcomingTransactionDTO {
        $exchangeRequestFilter = ExchangeRequestFilter::getInstance();

        foreach ($transactions as $transaction) {
            $duplicateExchangeRequest = $exchangeRequestFilter
                ->refresh()
                ->setPaymentTransactionId($transaction->id)
                ->setGivenMerchantId($exchangeRequest->given_merchant_id)
                ->first();

            if (null === $duplicateExchangeRequest) {
                return $transaction;
            }
        }

        return null;
    }
}
