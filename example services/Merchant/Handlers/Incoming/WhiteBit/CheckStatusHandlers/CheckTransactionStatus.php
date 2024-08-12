<?php

namespace App\Services\Merchant\Handlers\Incoming\WhiteBit\CheckStatusHandlers;

use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionStatus as BaseCheckTransactionStatus;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\WhiteBit\PrivateApi\Enums\TransactionStatus;
use App\Services\WhiteBit\PrivateApi\ValueObjects\Transaction;

class CheckTransactionStatus extends BaseCheckTransactionStatus
{
    public function __construct(
        private readonly TransactionsStorage $transactionsStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();

        /** @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if ($transaction->status !== TransactionStatus::SUCCESSFUL) {
            return false;
        }

        $this->transactionsStorage->forget($exchangeRequest->getUniqString());

        return true;
    }
}
