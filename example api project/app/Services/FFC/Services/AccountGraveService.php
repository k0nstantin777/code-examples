<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\CreateAccountGrave;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateAccountGraveRequestDto;
use Illuminate\Validation\ValidationException;

class AccountGraveService
{
    public function __construct(
        private readonly CreateAccountGrave $createAccountGraveEndpoint,
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function create(CreateAccountGraveRequestDto $dto): array
    {
        return $this->createAccountGraveEndpoint->execute($dto);
    }
}
