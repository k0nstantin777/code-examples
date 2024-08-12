<?php

namespace App\Services\Merchant\Handlers\Incoming\AdvancedCash\CheckStatusHandlers;

use App\Services\AdvancedCash\Enums\TransactionStatus;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionStatus as BaseCheckTransactionStatus;

class CheckTransactionStatus extends BaseCheckTransactionStatus
{
    public function __construct(
        private PaymentAddressesStorage $paymentAddressesStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();

        /** @var \outcomingTransactionDTO $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if ((string) $transaction->status !== TransactionStatus::COMPLETED) {
            return false;
        }

        $this->paymentAddressesStorage->forget($exchangeRequest->getUniqString());

        return true;
    }
}
