<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetCurrencyRequestDto;
use App\Services\Exchanger\ValueObjects\Currency;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class GetCurrency extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : Currency
    {
        /** @var GetCurrencyRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('currencies.show', [
            'id' => $dto->id,
        ]);

        return new Currency($response);
    }
}
