<?php

namespace App\Services\Merchant\Handlers\Incoming\WhiteBit\CheckStatusHandlers;

use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckReceivedAmount as BaseCheckReceivedAmount;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Money\MoneyFloatService;
use App\Services\Money\ValueObjects\MoneyFloat;
use App\Services\WhiteBit\PrivateApi\ValueObjects\Transaction;

class CheckReceivedAmount extends BaseCheckReceivedAmount
{
    public function __construct(
        private readonly MoneyFloatService $moneyFloatService,
        private readonly TransactionsStorage $transactionsStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /** @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        $receivedAmount = floatSum($transaction->amount, $transaction->fee);
        $checkStatusRequest->set(CheckStatusRequestProperty::RECEIVED_AMOUNT, $receivedAmount);

        if ($receivedAmount > 0 &&
            $this->moneyFloatService->compare(
                new MoneyFloat($receivedAmount),
                new MoneyFloat($exchangeRequest->given_sum)
            ) === -1
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }
}
