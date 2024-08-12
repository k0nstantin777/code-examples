<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ListRequestDto;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use App\Services\FFC\ValueObjects\CategoryList as CategoryListValueObject;
use Illuminate\Validation\ValidationException;

class CategoryList extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return CategoryListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function execute(...$arguments): CategoryListValueObject
    {
        /* @var ListRequestDto $categoryListDto */
        [$categoryListDto] = $arguments;

         $response = $this->jsonRpcClient->send('categories', [
             'sort' => $categoryListDto->sort,
             'limit' => $categoryListDto->limit,
             'offset' => $categoryListDto->offset,
             'sort_direction' => $categoryListDto->sortDirection,
         ]);

        return new CategoryListValueObject($response);
    }
}
