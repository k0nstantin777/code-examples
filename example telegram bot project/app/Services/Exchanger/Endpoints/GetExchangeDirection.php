<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRequestDto;
use App\Services\Exchanger\ValueObjects\Currency;
use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionAccessDetails;
use App\Services\Exchanger\ValueObjects\ExchangeFormAttribute;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class GetExchangeDirection extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : ExchangeDirection
    {
        /** @var GetExchangeDirectionRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('exchange-directions.show', [
            'id' => $dto->id,
            'customer_id' => $dto->customerId,
            'with_inactive' => $dto->withInactive,
        ]);

        $attributes = [];

        foreach ($response['form_attributes'] as $attributeData) {
            $attributes[] = new ExchangeFormAttribute($attributeData);
        }

        return new ExchangeDirection(
            id: $response['id'],
            given_currency: new Currency($response['given_currency']),
            received_currency: new Currency($response['received_currency']),
            given_currency_rate: $response['given_currency_rate'],
            received_currency_rate: $response['received_currency_rate'],
            given_min_sum: $response['given_min_sum'],
            given_max_sum: $response['given_max_sum'],
            received_min_sum: $response['received_min_sum'],
            received_max_sum: $response['received_max_sum'],
            commission_value: $response['commission_value'],
            access: new ExchangeDirectionAccessDetails($response['access']),
            form_attributes: $attributes,
        );
    }
}
