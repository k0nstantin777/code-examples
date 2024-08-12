<?php

namespace App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers;

use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPaymentDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaid;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;

abstract class CheckTransactionStatus extends CheckStatusState
{
    protected function error(CheckStatusRequest $checkStatusRequest): void
    {
        // awaiting
    }

    protected function success(CheckStatusRequest $checkStatusRequest): void
    {
        event(
            new SuccessPaid(
                $checkStatusRequest->getExchangeRequest(),
                new ExchangeRequestPaymentDto(
                    $checkStatusRequest->get(CheckStatusRequestProperty::TXID, TransactionIdPlaceholder::NOT_FOUND),
                )
            )
        );
    }
}
