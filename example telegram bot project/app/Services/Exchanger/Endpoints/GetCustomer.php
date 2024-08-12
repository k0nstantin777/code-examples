<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetCustomerRequestDto;
use App\Services\Exchanger\ValueObjects\Customer;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class GetCustomer extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : Customer
    {
        /** @var GetCustomerRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('customers.show', array_filter([
            'id' => $dto->id,
            'email' => $dto->email,
        ]));

        return new Customer(
            id: $response['id'],
            name: $response['name'],
            email: $response['email'],
        );
    }
}
