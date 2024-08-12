<?php


namespace App\Services\Merchant\Handlers;

use App\Models\Currency\Currency;
use App\Models\Exchange\ExchangeRequest;

interface OutgoingMerchantHandler extends MerchantHandler
{
    public function makePayout(ExchangeRequest $exchangeRequest) : void;
    public function findPayoutTransactionId(ExchangeRequest $exchangeRequest) : string;
    public function fetchReservesByCurrency(Currency $currency) : string;
}
