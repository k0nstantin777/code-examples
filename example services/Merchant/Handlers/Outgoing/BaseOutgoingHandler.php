<?php


namespace App\Services\Merchant\Handlers\Outgoing;

use App\Models\Currency\Currency;
use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Handlers\OutgoingMerchantHandler;

abstract class BaseOutgoingHandler implements OutgoingMerchantHandler
{
    public function getRequiredExchangeAttributeCodes(): array
    {
        return [];
    }

    public function findPayoutTransactionId(ExchangeRequest $exchangeRequest) : string
    {
        return $exchangeRequest->payout_transaction_id ?? '';
    }

    public function fetchReservesByCurrency(Currency $currency) : string
    {
        return $currency->reserve;
    }
}
