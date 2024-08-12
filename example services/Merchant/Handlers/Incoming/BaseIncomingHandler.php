<?php


namespace App\Services\Merchant\Handlers\Incoming;

use App\Services\Merchant\Handlers\IncomingMerchantHandler;

abstract class BaseIncomingHandler implements IncomingMerchantHandler
{
    public function getRequiredExchangeAttributeCodes(): array
    {
        return [];
    }
}
