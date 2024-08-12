<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\ProductList;
use App\Services\FFC\Endpoints\UpdateProduct;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ProductsRequestDto;
use App\Services\FFC\RequestDTOs\UpdateProductRequestDto;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use App\Services\FFC\ValueObjects\ProductList as ProductListValueObject;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        private readonly ProductList $productListEndpoint,
        private readonly UpdateProduct $updateProductEndpoint,
    ) {
    }

    /**
     * @param ProductsRequestDto $dto
     * @return ProductListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getProductList(ProductsRequestDto $dto): ProductListValueObject
    {
        return $this->productListEndpoint->execute($dto);
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function updateProduct(UpdateProductRequestDto $dto): string
    {
        return $this->updateProductEndpoint->execute($dto);
    }
}
