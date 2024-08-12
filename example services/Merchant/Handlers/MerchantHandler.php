<?php


namespace App\Services\Merchant\Handlers;

interface MerchantHandler
{
    public function getRequiredExchangeAttributeCodes() : array;
}
