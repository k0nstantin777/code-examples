<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\CemeteryList;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CemeteriesRequestDto;
use App\Services\FFC\ValueObjects\CemeteryList as CemeteryListValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Validation\ValidationException;

class CemeteryService
{
    public function __construct(
        private readonly CemeteryList $cemeteryListEndpoint,
    ) {
    }

    /**
     * @param CemeteriesRequestDto $dto
     * @return CemeteryListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function getList(CemeteriesRequestDto $dto): CemeteryListValueObject
    {
        return $this->cemeteryListEndpoint->execute($dto);
    }
}
