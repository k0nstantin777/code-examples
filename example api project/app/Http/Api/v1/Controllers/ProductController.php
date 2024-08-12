<?php

namespace App\Http\Api\v1\Controllers;

use App\Http\Api\v1\Requests\ProductsPaginationRequest;
use App\Http\Api\v1\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ProductsRequestDto;
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
     * Get list all products
     *
     * @responseFile storage/responses/products/index.json
     *
     * @param ProductsPaginationRequest $request
     * @param ProductService $productService
     * @return JsonResponse
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function index(
        ProductsPaginationRequest $request,
        ProductService $productService,
    ): JsonResponse {

        $productList = $productService->getProductList(ProductsRequestDto::from([
            'sort' => $request->getSort(),
            'sortDirection' => $request->getSortDirection(),
            'limit' => $request->getLimit(),
            'offset' => $request->getOffset(),
            'includes' => $request->getIncludes(),
            'in_stock' => $request->getInStock(),
            'search' => $request->getSearch(),
            'userId' => $request->user()->ffc_id,
            'categoryId' => $request->getCategoryId(),
        ]));

        $result = [
            'data' => ProductResource::collection($productList->getData()),
            'meta' => [
                'offset' => $productList->getMeta()->getOffset(),
                'limit' => $productList->getMeta()->getLimit(),
                'total' => $productList->getMeta()->getTotal(),
            ]
        ];

        return response()->apiSuccess($result);
    }
}
