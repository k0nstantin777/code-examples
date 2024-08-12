<?php

namespace App\Services\Merchant\Handlers\Incoming\EasyTransfer\CheckStatusHandlers;

use App\Services\EasyTransfer\ValueObjects\InvoiceStatus;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckPaymentBankCard as BaseCheckPaymentBankCard;

class CheckPaymentBankCard extends BaseCheckPaymentBankCard
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /** @var InvoiceStatus $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if (null === $transaction->getPaymentData()) {
            return true; // invoice don`t paid yet and card number not specified.
        }

        $partiallyCardNumber = $transaction->getPaymentData()->getCardPanMasked();
        $checkStatusRequest->set('cardNumber', $partiallyCardNumber);
        $cardNumberInExchangeRequest = $exchangeRequest->given_requisites;

        if (substr($partiallyCardNumber, -4, 4) !==
            substr($cardNumberInExchangeRequest, -4, 4)
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }
}
