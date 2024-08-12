<?php


namespace App\Services\Merchant\Handlers\Outgoing\WhiteBit\CryptoCurrency;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Handlers\Outgoing\WhiteBit\WhiteBitOutgoingHandler;
use App\Services\WhiteBit\PrivateApi\RequestDTOs\CreateWithdrawRequestDto;
use Illuminate\Support\Facades\Log;

class WhiteBitCryptoCurrencyOutgoingHandler extends WhiteBitOutgoingHandler
{
    protected function createTransaction(ExchangeRequest $exchangeRequest) : bool
    {
        try {
            $dto = new CreateWithdrawRequestDto(
                address: $exchangeRequest->received_requisites,
                currency: $exchangeRequest->receivedCurrency->code,
                network: $exchangeRequest->receivedCurrency->network ?? '',
                amount: $exchangeRequest->received_sum,
                unique_id: $this->getTransactionUniqueId($exchangeRequest),
            );

            return $this->whiteBitApiService->createWithdraw($dto);
        } catch (\Exception $e) {
            Log::channel('payouts-log')->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}
