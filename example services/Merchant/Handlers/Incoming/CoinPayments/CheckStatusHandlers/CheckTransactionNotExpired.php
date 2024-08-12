<?php

namespace App\Services\Merchant\Handlers\Incoming\CoinPayments\CheckStatusHandlers;

use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionNotExpired
    as BaseCheckTransactionNotExpired;

class CheckTransactionNotExpired extends BaseCheckTransactionNotExpired
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
    ) {
    }

    protected function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $transactionInfo = $checkStatusRequest->get('transactionInfo');

        $transaction = $this->transactionsStorage->get($exchangeRequest->getUniqString());

        if ($transaction === null || $transactionInfo->getStatus() < 0) {
            return false;
        }

        $checkStatusRequest->set(CheckStatusRequestProperty::TRANSACTION, $transaction);
        return true;
    }
}
