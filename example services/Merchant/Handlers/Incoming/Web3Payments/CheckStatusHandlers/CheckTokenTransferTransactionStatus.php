<?php

namespace App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers;

use App\Services\Etherscan\ValueObjects\TokenTransferTransaction;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionStatus as BaseCheckTransactionStatus;

class CheckTokenTransferTransactionStatus extends BaseCheckTransactionStatus
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();

        /** @var TokenTransferTransaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        $needConfirmations = config(
            'exchange-processing.need_confirmations.' . $exchangeRequest->givenCurrency->code,
            config('exchange-processing.need_confirmations_default')
        );

        if ($transaction->getConfirmations() < $needConfirmations) {
            return false;
        }

        $this->transactionsStorage->forget($exchangeRequest->getUniqString());

        return true;
    }
}
