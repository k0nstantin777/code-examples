<?php

namespace App\Services\Merchant\Handlers\Incoming\Payeer\CheckStatusHandlers;

use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Money\MoneyFloatService;
use App\Services\Payeer\ValueObjects\Transaction;
use App\Services\Money\ValueObjects\MoneyFloat;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckReceivedAmount as BaseCheckReceivedAmount;

class CheckReceivedAmount extends BaseCheckReceivedAmount
{
    public function __construct(
        private MoneyFloatService $moneyFloatService,
        private PaymentAddressesStorage $paymentAddressesStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /* @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        $receivedAmount = $transaction->getAmount();
        $checkStatusRequest->set(CheckStatusRequestProperty::RECEIVED_AMOUNT, $receivedAmount);

        if ($receivedAmount > 0 &&
            $this->moneyFloatService->compare(
                new MoneyFloat($receivedAmount),
                new MoneyFloat($exchangeRequest->given_sum)
            ) === -1
        ) {
            $this->paymentAddressesStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }
}
