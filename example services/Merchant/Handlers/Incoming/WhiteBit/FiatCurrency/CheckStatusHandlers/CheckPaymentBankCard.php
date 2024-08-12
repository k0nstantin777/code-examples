<?php

namespace App\Services\Merchant\Handlers\Incoming\WhiteBit\FiatCurrency\CheckStatusHandlers;

use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckPaymentBankCard as BaseCheckPaymentBankCard;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\WhiteBit\PrivateApi\ValueObjects\Transaction;

class CheckPaymentBankCard extends BaseCheckPaymentBankCard
{
    public function __construct(
        private readonly TransactionsStorage $transactionsStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /** @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if (!$transaction->address) {
            return true; // invoice don`t paid yet and card number not specified.
        }

        $checkStatusRequest->set('cardNumber', $transaction->address);

        $cardNumberInExchangeRequest = $exchangeRequest->given_requisites;

        if (substr($transaction->address, -3, 3) !==
            substr($cardNumberInExchangeRequest, -3, 3)
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }
}
