<?php

namespace App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers;

use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPaymentDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidErrorOccurred;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;

abstract class CheckPaymentBankCard extends CheckStatusIntermediateState
{
    public function error(CheckStatusRequest $checkStatusRequest): void
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $cardNumber = $checkStatusRequest->get('cardNumber', '');

        event(new PaidErrorOccurred(
            $exchangeRequest,
            new ExchangeRequestPaymentDto(
                $checkStatusRequest->get(CheckStatusRequestProperty::TXID, TransactionIdPlaceholder::NOT_FOUND),
                __(
                    'Payment card number: :card_number does not match with specified in exchange request',
                    [
                        'card_number' => $cardNumber,
                    ]
                )
            )
        ));
    }
}
