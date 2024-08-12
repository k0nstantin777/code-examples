<?php

namespace App\Http\Api\v1\Controllers;

use App\Domains\Order\Services\PreparedOrderService;
use App\Http\Api\v1\Requests\CreateOrderRequest;
use App\Http\Api\v1\Requests\OrdersPaginationRequest;
use App\Http\Api\v1\Resources\OrderResource;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateOrderRequestDto;
use App\Services\FFC\RequestDTOs\OrdersRequestDto;
use App\Services\FFC\Services\OrderService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group Orders
 *
 * APIs for managing orders
 */
class OrderController extends Controller
{
    /**
     * Get list account orders
     *
     * @responseFile storage/responses/orders/index.json
     *
     * @param OrdersPaginationRequest $request
     * @param OrderService $orderService
     * @return JsonResponse
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
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
            'userId' => $request->user()->ffc_id,
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

    /**
     * Create order
     *
     * Before call this endpoint, need call <a href="/docs/#orders-POSTapi-v1-orders-prepare">prepare order</a>
     *
     * @responseFile storage/responses/orders/create.json
     * @bodyParam ship_rate object required Selected ship rate from the <a href="/docs/#orders-POSTapi-v1-orders-prepare">Order Prepare Response</a>
     *
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws InvalidSchemaException
     */
    public function store(
        CreateOrderRequest $request,
        OrderService $orderService,
        PreparedOrderService $preparedOrderService,
    ) {

        $preparedOrder = $preparedOrderService->getById($request->get('prepared_order_id'));
        $orderData = $preparedOrder->order;

        $result = $orderService->create(new CreateOrderRequestDto([
            'user_id' => $preparedOrder->user->ffc_id,
            'delivery_address_id' => $orderData['delivery_address_id'] ?? null,
            'payment_address_id' =>  $orderData['payment_address_id'],
            'shipping_type' =>  $orderData['shipping_type'],
            'products' =>  $orderData['products'],
            'grave_id' =>  $orderData['grave_id'],
            'comment' =>  $orderData['comment'],
            'coupon' =>  $orderData['coupon'],
            'ship_rate' => $request->get('ship_rate'),
        ]));

        $preparedOrderService->delete($preparedOrder->id);

        return response()->apiSuccess($result);
    }
}
