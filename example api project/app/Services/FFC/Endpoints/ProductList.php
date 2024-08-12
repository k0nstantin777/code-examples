<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ProductsRequestDto;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use App\Services\FFC\ValueObjects\ProductList as ProductListValueObject;
use Illuminate\Validation\ValidationException;

class ProductList extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return ProductListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function execute(...$arguments): ProductListValueObject
    {
        /* @var ProductsRequestDto $productRequestDto */
        [$productRequestDto] = $arguments;

         $response = $this->jsonRpcClient->send('products', [
            'sort' => $productRequestDto->sort,
            'limit' => $productRequestDto->limit,
            'offset' => $productRequestDto->offset,
            'sort_direction' => $productRequestDto->sortDirection,
            'includes' => $productRequestDto->includes,
            'in_stock' => $productRequestDto->inStock,
            'search' => $productRequestDto->search,
            'user_id' => $productRequestDto->userId,
            'category_id' => $productRequestDto->categoryId,
         ]);

         return new ProductListValueObject($response);
    }
}
