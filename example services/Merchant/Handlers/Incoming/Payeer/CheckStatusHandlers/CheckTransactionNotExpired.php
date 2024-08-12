<?php

namespace App\Services\Merchant\Handlers\Incoming\Payeer\CheckStatusHandlers;

use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionNotExpired
    as BaseCheckTransactionNotExpired;
use App\Services\Payeer\Enums\TransactionStatus;
use App\Services\Payeer\ValueObjects\Transaction;

class CheckTransactionNotExpired extends BaseCheckTransactionNotExpired
{
    public function __construct(
        private PaymentAddressesStorage $paymentAddressesStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /* @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if ($transaction->getStatus() === TransactionStatus::CANCELED ||
            $exchangeRequest->is_await_payment_expired
        ) {
            $this->paymentAddressesStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }
}
