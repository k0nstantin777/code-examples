<?php

namespace App\Http\Api\v1\PrivateApi\Controllers;

use App\Http\Api\v1\PrivateApi\Requests\OrdersPaginationRequest;
use App\Http\Api\v1\PrivateApi\Resources\OrderResource;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\OrdersRequestDto;
use App\Services\FFC\Services\OrderService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * @param OrdersPaginationRequest $request
     * @param OrderService $orderService
     * @return JsonResponse
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function index(
        OrdersPaginationRequest $request,
        OrderService $orderService,
    ): JsonResponse {
        $orderList = $orderService->getList(OrdersRequestDto::from([
            'sort' => $request->getSort(),
            'sortDirection' => $request->getSortDirection(),
            'limit' => $request->getLimit(),
            'offset' => $request->getOffset(),
            'includes' => $request->getIncludes(),
            'status' => $request->getStatus(),
            'search' => $request->getSearch(),
            'userId' => $request->getUserId(),
            'from' => $request->getFrom(),
            'to' => $request->getTo()
        ]));

        $result = [
            'data' => OrderResource::collection($orderList->getData()),
            'meta' => [
                'offset' => $orderList->getMeta()->getOffset(),
                'limit' => $orderList->getMeta()->getLimit(),
                'total' => $orderList->getMeta()->getTotal(),
            ]
        ];

        return response()->apiSuccess($result);
    }
}
