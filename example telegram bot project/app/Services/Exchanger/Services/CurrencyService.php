<?php

namespace App\Services\Exchanger\Services;

use App\Services\Exchanger\Endpoints\GetCurrency;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetCurrencyRequestDto;
use App\Services\Exchanger\ValueObjects\Currency;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class CurrencyService
{
    public function __construct(
        private readonly GetCurrency $getCurrencyEndpoint
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function get(GetCurrencyRequestDto $dto) : Currency
    {
        return $this->getCurrencyEndpoint->execute($dto);
    }
}
