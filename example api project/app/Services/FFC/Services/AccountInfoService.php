<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\AccountInfo;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\AccountInfoRequestDto;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use App\Services\FFC\ValueObjects\AccountInfo as AccountInfoValueObject;
use Illuminate\Validation\ValidationException;

class AccountInfoService
{
    public function __construct(
        private readonly AccountInfo $accountInfoEndpoint,
    ) {
    }

    /**
     * @param AccountInfoRequestDto $dto
     * @return AccountInfoValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function get(AccountInfoRequestDto $dto): AccountInfoValueObject
    {
        return $this->accountInfoEndpoint->execute($dto);
    }
}
