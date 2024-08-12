<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\RejectExchangeRequestRequestDto;
use Illuminate\Validation\ValidationException;

class RejectExchangeRequest extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     */
    public function execute(...$arguments) : bool
    {
        /** @var RejectExchangeRequestRequestDto $dto */
        [$dto] = $arguments;

        $data = [
            'id' => $dto->id,
            'customer_id' => $dto->customerId,
        ];

        $response = $this->jsonRpcClient->send('exchange-requests-processing.reject', $data);

        return $response['success'] ?? false;
    }
}
