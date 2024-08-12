<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetCustomerRequestDto;
use App\Services\Exchanger\ValueObjects\CustomerExtended;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class GetCustomerExtended extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : CustomerExtended
    {
        /** @var GetCustomerRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('customers.show', array_filter([
            'id' => $dto->id,
            'email' => $dto->email,
            'has_extended_info' => true,
        ]));

        return new CustomerExtended(
            id: $response['id'],
            name: $response['name'],
            email: $response['email'],
            balance: $response['balance'],
            exchange_bonus: $response['exchange_bonus'],
            phone: $response['phone'],
        );
    }
}
