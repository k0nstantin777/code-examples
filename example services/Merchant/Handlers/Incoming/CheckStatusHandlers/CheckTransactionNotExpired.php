<?php

namespace App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers;

use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPaymentDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidErrorOccurred;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;

abstract class CheckTransactionNotExpired extends CheckStatusIntermediateState
{
    protected function error(CheckStatusRequest $checkStatusRequest): void
    {
        event(new PaidErrorOccurred(
            $checkStatusRequest->getExchangeRequest(),
            new ExchangeRequestPaymentDto(
                $checkStatusRequest->get(CheckStatusRequestProperty::TXID, TransactionIdPlaceholder::NOT_FOUND),
                __('Transaction expired, please contact support')
            )
        ));
    }
}
