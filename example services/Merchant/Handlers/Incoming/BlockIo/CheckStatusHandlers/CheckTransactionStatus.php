<?php

namespace App\Services\Merchant\Handlers\Incoming\BlockIo\CheckStatusHandlers;

use App\Services\BlockIO\ValueObjects\ReceivedTransaction;
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

    private const MIN_CONFIDENCE = 0.95; // Block IO recommended: 0.9 - 0.99

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /** @var ReceivedTransaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        $needsConfirmation = config(
            'exchange-processing.need_confirmations.' . $exchangeRequest->givenCurrency->code,
            config('exchange-processing.need_confirmations_default')
        );

        if ($transaction->getConfidence() >= self::MIN_CONFIDENCE &&
            $transaction->getConfirmations() >= $needsConfirmation
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            $this->paymentAddressesStorage->forget($exchangeRequest->payment_address);
            return true;
        }

        return false;
    }
}
