<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\CategoryList;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ListRequestDto;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use App\Services\FFC\ValueObjects\CategoryList as CategoryListValueObject;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(
        private readonly CategoryList $categoryListEndpoint,
    ) {
    }

    /**
     * @param ListRequestDto $dto
     * @return CategoryListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function getList(ListRequestDto $dto): CategoryListValueObject
    {
        return $this->categoryListEndpoint->execute($dto);
    }
}
