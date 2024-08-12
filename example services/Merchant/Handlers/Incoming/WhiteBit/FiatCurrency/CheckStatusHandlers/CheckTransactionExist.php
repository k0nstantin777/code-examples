<?php

namespace App\Services\Merchant\Handlers\Incoming\WhiteBit\FiatCurrency\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Handlers\Incoming\WhiteBit\CheckStatusHandlers\CheckTransactionExist
    as BaseCheckTransactionExist;
use App\Services\WhiteBit\PrivateApi\Enums\TransactionMethod;
use App\Services\WhiteBit\PrivateApi\Exceptions\WhiteBitApiException;
use App\Services\WhiteBit\PrivateApi\RequestDTOs\HistoryRequestDto;
use App\Services\WhiteBit\PrivateApi\ValueObjects\Transaction;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    /**
     * @param ExchangeRequest $exchangeRequest
     * @return Transaction|null
     */
    protected function getTransaction(ExchangeRequest $exchangeRequest) : ?Transaction
    {
        try {
            $dto = new HistoryRequestDto(
                unique_id: $exchangeRequest->token,
                method: TransactionMethod::DEPOSIT,
                currency: $exchangeRequest->givenCurrency->code,
            );

            $history = $this->whiteBitApiService->getHistory($dto);

            foreach ($history->records as $transaction) {
                if ($transaction->uniqueId === $exchangeRequest->token &&
                    $transaction->method === TransactionMethod::DEPOSIT
                ) {
                    return $transaction;
                }
            }
        } catch (WhiteBitApiException|\JsonException|UnknownProperties) {
        }

        return null;
    }
}
