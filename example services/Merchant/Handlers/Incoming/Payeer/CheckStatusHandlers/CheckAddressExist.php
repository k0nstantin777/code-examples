<?php

namespace App\Services\Merchant\Handlers\Incoming\Payeer\CheckStatusHandlers;

use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckAddressExist as BaseCheckAddressExist;
use App\Services\Payeer\ValueObjects\Transaction;

class CheckAddressExist extends BaseCheckAddressExist
{
    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        /* @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);
        $account = $checkStatusRequest->getExchangeRequest()->givenCurrency->payment_requisites;

        return $account === $transaction->getDestinationAccount()->getUsername();
    }
}
