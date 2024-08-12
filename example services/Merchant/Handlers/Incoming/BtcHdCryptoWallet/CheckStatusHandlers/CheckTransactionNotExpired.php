<?php

namespace App\Services\Merchant\Handlers\Incoming\BtcHdCryptoWallet\CheckStatusHandlers;

use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionNotExpired
    as BaseCheckTransactionNotExpired;

class CheckTransactionNotExpired extends BaseCheckTransactionNotExpired
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
        private PaymentAddressesStorage $paymentAddressesStorage,
    ) {
    }

    protected function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();

        if ($exchangeRequest->is_await_payment_expired) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            $this->paymentAddressesStorage->forget($exchangeRequest->payment_address);
            return false;
        }

        return true;
    }
}
