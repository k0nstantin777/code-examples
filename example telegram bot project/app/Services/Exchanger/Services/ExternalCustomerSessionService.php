<?php

namespace App\Services\Exchanger\Services;

use App\Services\Exchanger\Endpoints\GetExternalCustomerSession;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExternalCustomerSessionRequestDto;
use App\Services\Exchanger\ValueObjects\ExternalCustomerSession;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ExternalCustomerSessionService
{
    public function __construct(
        private readonly GetExternalCustomerSession $getExternalCustomerSessionEndpoint
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function get(GetExternalCustomerSessionRequestDto $dto) : ExternalCustomerSession
    {
        return $this->getExternalCustomerSessionEndpoint->execute($dto);
    }
}
