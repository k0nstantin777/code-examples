<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CemeteriesRequestDto;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use App\Services\FFC\ValueObjects\CemeteryList as CemeteryListValueObject;
use Illuminate\Validation\ValidationException;

class CemeteryList extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return CemeteryListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function execute(...$arguments): CemeteryListValueObject
    {
        /* @var CemeteriesRequestDto $dto */
        [$dto] = $arguments;

         $response = $this->jsonRpcClient->send('cemeteries', [
            'city' => $dto->city,
            'state' => $dto->state,
            'is_active' => $dto->isActive,
            'sort' => $dto->sort,
            'limit' => $dto->limit,
            'offset' => $dto->offset,
            'sort_direction' => $dto->sortDirection,
         ]);

        return new CemeteryListValueObject($response);
    }
}
