<?php


namespace App\Services\Merchant\Handlers\Outgoing\WhiteBit\FiatCurrency;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Handlers\Outgoing\WhiteBit\WhiteBitOutgoingHandler;
use App\Services\WhiteBit\PrivateApi\Enums\ProviderName;
use App\Services\WhiteBit\PrivateApi\RequestDTOs\CreateWithdrawRequestDto;
use Illuminate\Support\Facades\Log;

class WhiteBitFiatCurrencyOutgoingHandler extends WhiteBitOutgoingHandler
{
    protected function createTransaction(ExchangeRequest $exchangeRequest) : bool
    {
        try {
            $dto = new CreateWithdrawRequestDto(
                address: $exchangeRequest->received_requisites,
                currency: $exchangeRequest->receivedCurrency->code,
                amount: $exchangeRequest->received_sum,
                unique_id: $this->getTransactionUniqueId($exchangeRequest),
                provider_name: ProviderName::VISAMASTER,
            );

            return $this->whiteBitApiService->createWithdraw($dto);
        } catch (\Exception $e) {
            Log::channel('payouts-log')->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}
