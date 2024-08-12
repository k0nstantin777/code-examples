<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\PayExchangeRequestRequestDto;
use Illuminate\Validation\ValidationException;

class PayExchangeRequest extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     */
    public function execute(...$arguments) : bool
    {
        /** @var PayExchangeRequestRequestDto $dto */
        [$dto] = $arguments;

        $data = [
            'id' => $dto->id,
            'customer_id' => $dto->customerId,
            'transaction_id' => $dto->transactionId,
            'payment_address' => $dto->paymentAddress,
        ];

        $response = $this->jsonRpcClient->send('exchange-requests-processing.pay', $data);

        return $response['success'] ?? false;
    }
}
