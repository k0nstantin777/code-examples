<?php


namespace App\Services\Merchant\Handlers\Outgoing;

use App\Enums\ExchangeAttributeCode;
use App\Models\Exchange\ExchangeRequest;

class ManualOutgoingHandler extends BaseOutgoingHandler
{
    public function makePayout(ExchangeRequest $exchangeRequest) : void
    {
        // For manual merchant nothing to do
    }

    public function getRequiredExchangeAttributeCodes(): array
    {
        return [
            ExchangeAttributeCode::REQUISITES_RECEIVED_CURRENCY,
        ];
    }
}
