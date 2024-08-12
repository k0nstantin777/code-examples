<?php

namespace App\Services\Merchant\Handlers\Incoming\PerfectMoney\CheckStatusHandlers;

use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPaymentDto;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionStatus as BaseCheckTransactionStatus;
use App\Services\PerfectMoney\ValueObjects\Transaction;

class CheckTransactionStatus extends BaseCheckTransactionStatus
{
    public function __construct(
        private PaymentAddressesStorage $paymentAddressesStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();

        /** @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if ($transaction->getBatch()) {
            $this->paymentAddressesStorage->forget($exchangeRequest->getUniqString());
            return true;
        }

        return false;
    }
}
