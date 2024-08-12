<?php

namespace App\Services\Merchant\Handlers\Incoming\BtcHdCryptoWallet\CheckStatusHandlers;

use App\Services\BlockStream\ValueObjects\Utxo;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionStatus as BaseCheckTransactionStatus;

class CheckTransactionStatus extends BaseCheckTransactionStatus
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
        private PaymentAddressesStorage $paymentAddressesStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /** @var Utxo $utxo */
        $utxo = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if ($utxo->isConfirmed()) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            $this->paymentAddressesStorage->forget($exchangeRequest->payment_address);
            return true;
        }

        return false;
    }
}
