<?php

namespace App\Services\Exchanger\Services\ExchangeDirection;

use App\Services\Exchanger\Endpoints\GetExchangeDirectionRateByGivenSum;
use App\Services\Exchanger\Endpoints\GetExchangeDirectionRateByReceivedSum;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRateRequestDto;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionRate;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ExchangeDirectionRateService
{
    public function __construct(
        private readonly GetExchangeDirectionRateByGivenSum $getExchangeDirectionRateByGivenSumEndpoint,
        private readonly GetExchangeDirectionRateByReceivedSum $getExchangeDirectionRateByReceivedSumEndpoint,
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function calculateReceived(GetExchangeDirectionRateRequestDto $dto) : ExchangeDirectionRate
    {
        return $this->getExchangeDirectionRateByGivenSumEndpoint->execute($dto);
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function calculateGiven(GetExchangeDirectionRateRequestDto $dto) : ExchangeDirectionRate
    {
        return $this->getExchangeDirectionRateByReceivedSumEndpoint->execute($dto);
    }
}
