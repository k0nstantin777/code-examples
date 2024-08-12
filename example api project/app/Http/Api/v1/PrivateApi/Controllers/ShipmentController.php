<?php

namespace App\Http\Api\v1\PrivateApi\Controllers;

use App\Http\Api\v1\PrivateApi\Requests\ShipmentsPaginationRequest;
use App\Http\Api\v1\PrivateApi\Resources\ShipmentResource;
use App\Http\Controllers\Controller;
use App\Services\FFC\Enums\OrderType;
use App\Services\FFC\Enums\ShipmentStatus;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ShipmentsRequestDto;
use App\Services\FFC\Services\ShipmentService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group Shipment
 *
 * APIs for managing shipment
 */
class ShipmentController extends Controller
{
    /**
     * Get list account shipments
     *
     * @responseFile storage/responses/shipments/index.json
     *
     * @param ShipmentsPaginationRequest $request
     * @param ShipmentService $shipmentService
     * @return JsonResponse
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function index(
        ShipmentsPaginationRequest $request,
        ShipmentService $shipmentService,
    ): JsonResponse {
        $list = $shipmentService->getList(ShipmentsRequestDto::from([
            'sort' => $request->getSort(),
            'sortDirection' => $request->getSortDirection(),
            'limit' => $request->getLimit(),
            'offset' => $request->getOffset(),
            'type' => $request->getType() ? OrderType::from($request->getType()) : null,
            'status' => $request->getStatus() ? ShipmentStatus::from($request->getStatus()) : null,
            'search' => $request->getSearch(),
            'userId' => $request->getUserId(),
            'from' => $request->getFrom(),
            'to' => $request->getTo(),
            'shipFrom' => $request->getShipFrom(),
            'shipTo' => $request->getShipTo(),
            'includes' => $request->getIncludes(),
            'isShippingProcessing' => $request->getIsShippingProcessing(),
            'shippingProcessingFrom' => $request->getShippingProcessingFrom(),
            'shippingProcessingTo' => $request->getShippingProcessingTo(),
        ]));

        $result = [
            'data' => ShipmentResource::collection($list->getData()),
            'meta' => [
                'offset' => $list->getMeta()->getOffset(),
                'limit' => $list->getMeta()->getLimit(),
                'total' => $list->getMeta()->getTotal(),
            ]
        ];

        return response()->apiSuccess($result);
    }
}
