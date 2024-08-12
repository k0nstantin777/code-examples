<?php

namespace App\Http\Api\v1\Controllers;

use App\Domains\Order\DataTransferObjects\PreparedOrderDto;
use App\Domains\Order\Services\PreparedOrderService;
use App\Http\Api\v1\Requests\PrepareOrderRequest;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CalculateOrderRequestDto;
use App\Services\FFC\Services\OrderService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Validation\ValidationException;

/**
 * @group Orders
 *
 */
class PrepareOrderController extends Controller
{
    /**
     * Prepare order
     *
     * Use this endpoint for get calculate order and get ship rates.
     * @bodyParam products object[] required List of products (see <a href="/docs/#products">products</a> endpoint)
     * @responseFile storage/responses/orders/calculate.json
     *
     *
     * @throws JsonRpcErrorResponseException
     * @throws InvalidSchemaException
     * @throws ValidationException
     */
    public function store(
        PrepareOrderRequest $request,
        PreparedOrderService $preparedOrderService,
        OrderService $orderService,
    ) {

        $orderData = [
            'user_id' => $request->user()->ffc_id,
            'delivery_address_id' => $request->get('delivery_address_id'),
            'payment_address_id' => $request->get('payment_address_id'),
            'shipping_type' => $request->get('shipping_type'),
            'products' => $request->get('products'),
            'grave_id' => $request->get('grave_id'),
            'comment' => $request->get('comment'),
            'coupon' => $request->get('coupon'),
        ];

        $response = $orderService->calculate(new CalculateOrderRequestDto($orderData));

        $preparedOrder = $preparedOrderService->create(new PreparedOrderDto([
            'user_id' => $request->user()->id,
            'order' => array_merge($orderData, $response),
        ]));

        return response()->apiSuccess([
            'id' => $preparedOrder->id,
            'order' => $response,
        ]);
    }
}
