<?php

namespace App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers;

use App\Services\Etherscan\ValueObjects\TokenTransferTransaction;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckReceivedAmount as BaseCheckReceivedAmount;

class CheckTokenTransferReceivedAmount extends BaseCheckReceivedAmount
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

        $receivedAmount = floatDiv($transaction->getAmount(), 10**$transaction->getTokenDecimal());
        $checkStatusRequest->set(CheckStatusRequestProperty::RECEIVED_AMOUNT, $receivedAmount);

        if ($receivedAmount > 0 &&
            floatCompare(
                $receivedAmount,
                $exchangeRequest->given_sum
            ) === -1
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }
}
