<?php

namespace App\Http\Api\v1\PrivateApi\Controllers;

use App\Http\Api\v1\Requests\ProductUpdateRequest;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\UpdateProductRequestDto;
use App\Services\FFC\Services\ProductService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group Products
 *
 * APIs for managing products
 */
class ProductController extends Controller
{
    /**
     * Update products
     *
     * @responseFile storage/responses/products/update.json
     *
     * @param ProductUpdateRequest $request
     * @param ProductService $productService
     * @return JsonResponse
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function update(
        ProductUpdateRequest $request,
        ProductService $productService,
    ): JsonResponse {

        $result = $productService->updateProduct(new UpdateProductRequestDto([
            'code' => $request->getCode(),
            'stock_level' => $request->getStockLevel(),
        ]));

        return response()->apiSuccess($result);
    }
}
