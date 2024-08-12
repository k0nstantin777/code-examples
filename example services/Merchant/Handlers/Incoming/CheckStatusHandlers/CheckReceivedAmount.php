<?php

namespace App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers;

use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPaymentDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidErrorOccurred;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;

abstract class CheckReceivedAmount extends CheckStatusIntermediateState
{
    public function error(CheckStatusRequest $checkStatusRequest): void
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $receivedAmount = $checkStatusRequest->get(CheckStatusRequestProperty::RECEIVED_AMOUNT, 0);

        event(new PaidErrorOccurred(
            $exchangeRequest,
            new ExchangeRequestPaymentDto(
                $checkStatusRequest->get(CheckStatusRequestProperty::TXID, TransactionIdPlaceholder::NOT_FOUND),
                __(
                    'Received amount less than the amount specified in the application: '.
                        ':received_amount, please contact support',
                    [
                        'received_amount' => numberToString($receivedAmount),
                    ]
                )
            )
        ));
    }
}
