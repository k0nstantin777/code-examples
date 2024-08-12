<?php

namespace App\Services\Merchant\Handlers\Incoming\EasyTransfer\CheckStatusHandlers;

use App\Services\EasyTransfer\ValueObjects\InvoiceStatus;
use App\Services\EasyTransfer\Enums\InvoiceStatus as InvoiceStatusEnum;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionNotExpired
    as BaseCheckTransactionNotExpired;

class CheckTransactionNotExpired extends BaseCheckTransactionNotExpired
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /* @var InvoiceStatus $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if ($transaction->getStatus() === InvoiceStatusEnum::EXPIRED ||
            $exchangeRequest->is_await_payment_expired
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }
}
