<?php

namespace App\Services\Merchant\Handlers\Incoming\EasyTransfer\CheckStatusHandlers;

use App\Services\EasyTransfer\ValueObjects\InvoiceStatus;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\EasyTransfer\Enums\InvoiceStatus as InvoiceStatusEnum;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionStatus as BaseCheckTransactionStatus;

class CheckTransactionStatus extends BaseCheckTransactionStatus
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();

        /** @var InvoiceStatus $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if ($transaction->getStatus() !== InvoiceStatusEnum::PAID) {
            return false;
        }

        $this->transactionsStorage->forget($exchangeRequest->getUniqString());

        return true;
    }
}
