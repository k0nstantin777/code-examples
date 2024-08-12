<?php

namespace App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\Etherscan\EtherscanApiService;
use App\Services\Etherscan\RequestDTOs\TransactionListByAddressDto;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;
use App\Services\Etherscan\ValueObjects\Transaction;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        private EtherscanApiService $etherscanApiService
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $transaction = $this->getTransaction($exchangeRequest);

        $checkStatusRequest->set(
            CheckStatusRequestProperty::TXID,
            optional($transaction)->getTxHash() ?? TransactionIdPlaceholder::NOT_FOUND
        );

        if (null === $transaction) {
            return false;
        }

        $checkStatusRequest->set(CheckStatusRequestProperty::TRANSACTION, $transaction);
        return true;
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return Transaction|null
     */
    private function getTransaction(ExchangeRequest $exchangeRequest) : ?Transaction
    {
        try {
            $dto = new TransactionListByAddressDto($exchangeRequest->payment_address);

            $transactionList = $this->etherscanApiService->getTransactionListByAddress($dto);

            foreach ($transactionList as $transaction) {
                if ($this->isValidTransaction($transaction, $exchangeRequest)) {
                    return $transaction;
                }
            }
        } catch (\Exception) {
            // Nothing to do
        }

        return null;
    }

    private function isValidTransaction(Transaction $transaction, ExchangeRequest $exchangeRequest) : bool
    {
        return strtolower($transaction->getTxHash()) === strtolower($exchangeRequest->payment_transaction_id) &&
            strtolower($transaction->getTo()) === strtolower($exchangeRequest->payment_address) &&
            $transaction->getCreatedAt()->greaterThan($exchangeRequest->created_at) &&
            false === $transaction->isError() &&
            false === $this->hasDuplicate($exchangeRequest);
    }

    private function hasDuplicate(ExchangeRequest $exchangeRequest) : bool
    {
        $exchangeRequestFilter = ExchangeRequestFilter::getInstance();

        $exchangeRequests = $exchangeRequestFilter
            ->refresh()
            ->setPaymentTransactionId($exchangeRequest->payment_transaction_id)
            ->setGivenMerchantId($exchangeRequest->given_merchant_id)
            ->get();

        return $exchangeRequests->count() > 1;
    }
}
