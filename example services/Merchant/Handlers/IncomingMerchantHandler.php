<?php


namespace App\Services\Merchant\Handlers;

use App\Models\Exchange\ExchangeRequest;

interface IncomingMerchantHandler extends MerchantHandler
{
    public function getPaymentFormData(ExchangeRequest $exchangeRequest) : array;
    public function checkStatus(ExchangeRequest $exchangeRequest) : void;
}
