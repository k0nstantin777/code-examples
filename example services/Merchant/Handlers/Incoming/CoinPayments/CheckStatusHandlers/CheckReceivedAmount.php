<?php

namespace App\Services\Merchant\Handlers\Incoming\CoinPayments\CheckStatusHandlers;

use App\Services\CoinPayments\ValueObjects\TransactionInfo;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\ValueObjects\Transaction;
use App\Services\Money\MoneyFloatService;
use App\Services\Money\ValueObjects\MoneyFloat;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckReceivedAmount as BaseCheckReceivedAmount;

class CheckReceivedAmount extends BaseCheckReceivedAmount
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
        private MoneyFloatService $moneyFloatService,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /* @var TransactionInfo $transactionInfo */
        $transactionInfo = $checkStatusRequest->get('transactionInfo');

        /* @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        $receivedAmount = $transactionInfo->getReceivedAmount();
        $checkStatusRequest->set(CheckStatusRequestProperty::RECEIVED_AMOUNT, $receivedAmount);

        if ($receivedAmount > 0 &&
            $this->moneyFloatService->compare(
                new MoneyFloat($receivedAmount),
                new MoneyFloat($transaction->getAmount())
            ) === -1
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }
}
