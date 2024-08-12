<?php

namespace App\Services\Merchant\Handlers\Incoming\WhiteBit\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\WhiteBit\PrivateApi\ValueObjects\Transaction;
use App\Services\WhiteBit\PrivateApi\WhiteBitPrivateApiService;

abstract class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        protected readonly WhiteBitPrivateApiService $whiteBitApiService
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $transaction = $this->getTransaction($exchangeRequest);

        $checkStatusRequest->set(
            CheckStatusRequestProperty::TXID,
            optional($transaction)->hash ?? TransactionIdPlaceholder::NOT_FOUND
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
    abstract protected function getTransaction(ExchangeRequest $exchangeRequest) : ?Transaction;
}
