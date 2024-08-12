<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\CreateExchangeRequestRequestDto;
use Illuminate\Validation\ValidationException;

class CreateExchangeRequest extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     */
    public function execute(...$arguments) : string
    {
        /** @var CreateExchangeRequestRequestDto $dto */
        [$dto] = $arguments;

        $data = [
            'customer_id' => $dto->customerId,
            'email' => $dto->customerEmail,
            'direction_id' => $dto->exchangeDirectionId,
            'given_sum' => $dto->givenSum,
            'received_sum' => $dto->receivedSum,
            'commission' => $dto->commission,
        ];

        foreach ($dto->attributes as $key => $value) {
            $data[$key] = $value;
        }

        $response = $this->jsonRpcClient->send('exchange-requests.store', $data);

        return $response['token'];
    }
}
