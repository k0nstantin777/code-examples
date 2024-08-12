<?php

namespace App\Http\Api\v1\Controllers;

use App\Http\Api\v1\Requests\CategoriesPaginationRequest;
use App\Http\Api\v1\Resources\CategoryResource;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ListRequestDto;
use App\Services\FFC\Services\CategoryService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group Categories
 *
 * APIs for managing categories
 */
class CategoryController extends Controller
{
    /**
     * Get list all categories
     *
     * @responseFile storage/responses/categories/index.json
     *
     * @param CategoriesPaginationRequest $request
     * @param CategoryService $categoryService
     * @return JsonResponse
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function index(
        CategoriesPaginationRequest $request,
        CategoryService $categoryService,
    ): JsonResponse {

        $categoryList = $categoryService->getList(ListRequestDto::from([
            'sort' => $request->getSort(),
            'sortDirection' => $request->getSortDirection(),
            'limit' => $request->getLimit(),
            'offset' => $request->getOffset(),
        ]));

        $result = [
            'data' => CategoryResource::collection($categoryList->getData()),
            'meta' => [
                'offset' => $categoryList->getMeta()->getOffset(),
                'limit' => $categoryList->getMeta()->getLimit(),
                'total' => $categoryList->getMeta()->getTotal(),
            ]
        ];

        return response()->apiSuccess($result);
    }
}
