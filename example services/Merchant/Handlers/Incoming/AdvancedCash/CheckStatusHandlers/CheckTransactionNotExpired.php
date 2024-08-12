<?php

namespace App\Services\Merchant\Handlers\Incoming\AdvancedCash\CheckStatusHandlers;

use App\Services\AdvancedCash\Enums\TransactionStatus;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionNotExpired
    as BaseCheckTransactionNotExpired;
use outcomingTransactionDTO;

class CheckTransactionNotExpired extends BaseCheckTransactionNotExpired
{
    public function __construct(
        private PaymentAddressesStorage $paymentAddressesStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /* @var outcomingTransactionDTO $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if ((string) $transaction->status === TransactionStatus::CANCELED ||
            $exchangeRequest->is_await_payment_expired
        ) {
            $this->paymentAddressesStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }
}
