<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\CreateAccountAddress;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateAccountAddressRequestDto;
use Illuminate\Validation\ValidationException;

class AccountAddressService
{
    public function __construct(
        private readonly CreateAccountAddress $createAccountAddressEndpoint,
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function create(CreateAccountAddressRequestDto $dto): array
    {
        return $this->createAccountAddressEndpoint->execute($dto);
    }
}
