<?php

namespace App\Http\Api\v1\Controllers;

use App\Http\Api\v1\Requests\CemeteriesPaginationRequest;
use App\Http\Api\v1\Resources\CemeteryResource;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CemeteriesRequestDto;
use App\Services\FFC\Services\CemeteryService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group Cemeteries
 *
 * APIs for managing cemeteries
 */
class CemeteryController extends Controller
{
    /**
     * Get list all cemeteries
     *
     * @responseFile storage/responses/cemeteries/index.json
     *
     * @param CemeteriesPaginationRequest $request
     * @param CemeteryService $cemeteryService
     * @return JsonResponse
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function index(
        CemeteriesPaginationRequest $request,
        CemeteryService $cemeteryService,
    ): JsonResponse {

        $list = $cemeteryService->getList(CemeteriesRequestDto::from([
            'city' => $request->getCity(),
            'state' => $request->getState(),
            'sort' => $request->getSort(),
            'sort_direction' => $request->getSortDirection(),
            'limit' => $request->getLimit(),
            'offset' => $request->getOffset(),
        ]));

        $result = [
            'data' => CemeteryResource::collection($list->getData()),
            'meta' => [
                'offset' => $list->getMeta()->getOffset(),
                'limit' => $list->getMeta()->getLimit(),
                'total' => $list->getMeta()->getTotal(),
            ]
        ];

        return response()->apiSuccess($result);
    }
}
