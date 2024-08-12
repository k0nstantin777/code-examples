<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExternalCustomerSessionRequestDto;
use App\Services\Exchanger\ValueObjects\ExternalCustomerSession;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class GetExternalCustomerSession extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : ExternalCustomerSession
    {
        /** @var GetExternalCustomerSessionRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('external-customer-sessions.show', [
            'type' => $dto->type,
            'params' => $dto->params
        ]);

        return new ExternalCustomerSession(
            type: $response['type'],
            customer_id: $response['customer_id'],
            params: $response['params'],
            expired_at: Carbon::parse($response['expired_at']),
        );
    }
}
