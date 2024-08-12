<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\FlowerProgramsRequestDto;
use App\Services\FFC\ValueObjects\FlowerProgramList as FlowerProgramListValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Validation\ValidationException;

class FlowerProgramList extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return FlowerProgramListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function execute(...$arguments): FlowerProgramListValueObject
    {
        /* @var FlowerProgramsRequestDto $requestDto */
        [$requestDto] = $arguments;

         $response = $this->jsonRpcClient->send('flower-programs', [
            'sort' => $requestDto->sort,
            'limit' => $requestDto->limit,
            'offset' => $requestDto->offset,
            'sort_direction' => $requestDto->sortDirection,
            'includes' => $requestDto->includes,
            'status' => $requestDto->status,
            'search' => $requestDto->search,
            'user_id' => $requestDto->userId,
            'from' => $requestDto->from,
            'to' => $requestDto->to,
         ]);

         return new FlowerProgramListValueObject($response);
    }
}
