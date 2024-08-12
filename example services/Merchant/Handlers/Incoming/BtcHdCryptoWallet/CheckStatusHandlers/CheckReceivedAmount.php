<?php

namespace App\Services\Merchant\Handlers\Incoming\BtcHdCryptoWallet\CheckStatusHandlers;

use App\Services\BlockStream\ValueObjects\Utxo;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Money\MoneyFloatService;
use App\Services\Money\ValueObjects\MoneyFloat;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckReceivedAmount as BaseCheckReceivedAmount;

class CheckReceivedAmount extends BaseCheckReceivedAmount
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
        private PaymentAddressesStorage $paymentAddressesStorage,
        private MoneyFloatService $moneyFloatService,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();

        /** @var Utxo $utxo */
        $utxo = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        $receivedAmount = $utxo->getAmount();

        $checkStatusRequest->set(CheckStatusRequestProperty::RECEIVED_AMOUNT, $receivedAmount);

        if ($receivedAmount > 0 &&
            $this->moneyFloatService->isSecondGreater(
                new MoneyFloat($receivedAmount),
                new MoneyFloat($exchangeRequest->given_sum)
            )
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            $this->paymentAddressesStorage->forget($exchangeRequest->payment_address);
            return false;
        }

        return true;
    }
}
