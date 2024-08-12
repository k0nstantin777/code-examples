<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRateRequestDto;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionRate;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class GetExchangeDirectionRateByReceivedSum extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : ExchangeDirectionRate
    {
        /** @var GetExchangeDirectionRateRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('exchange-direction-rate.calculateGiven', [
            'id' => $dto->id,
            'customer_id' => $dto->customerId,
            'received_sum' => $dto->receivedSum,
        ]);

        return new ExchangeDirectionRate(
            given_sum: $response['given_sum'],
            received_sum: $response['received_sum'],
            commission: $response['commission'],
        );
    }
}
