<?php

namespace App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers;

use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPaymentDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidErrorOccurred;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;

abstract class CheckAddressExist extends CheckStatusIntermediateState
{
    protected function error(CheckStatusRequest $checkStatusRequest): void
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        event(new PaidErrorOccurred(
            $exchangeRequest,
            new ExchangeRequestPaymentDto(
                $checkStatusRequest->get(CheckStatusRequestProperty::TXID, TransactionIdPlaceholder::NOT_FOUND),
                __('Address :payment_address not found in auto check systems, please contact support', [
                    'payment_address' => $exchangeRequest->payment_address,
                ])
            )
        ));
    }
}
