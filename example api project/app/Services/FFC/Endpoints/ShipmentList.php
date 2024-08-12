<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ShipmentsRequestDto;
use App\Services\FFC\ValueObjects\ShipmentList as ShipmentListValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Validation\ValidationException;

class ShipmentList extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return ShipmentListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function execute(...$arguments): ShipmentListValueObject
    {
        /* @var ShipmentsRequestDto $requestDto */
        [$requestDto] = $arguments;

         $response = $this->jsonRpcClient->send('shipments', [
            'sort' => $requestDto->sort,
            'limit' => $requestDto->limit,
            'offset' => $requestDto->offset,
            'sort_direction' => $requestDto->sortDirection,
            'type' => $requestDto->type,
            'status' => $requestDto->status,
            'search' => $requestDto->search,
            'user_id' => $requestDto->userId,
            'from' => $requestDto->from,
            'to' => $requestDto->to,
            'ship_from' => $requestDto->shipFrom,
            'ship_to' => $requestDto->shipTo,
            'includes' => $requestDto->includes,
            'is_shipping_processing' => $requestDto->isShippingProcessing,
            'shipping_processing_from' => $requestDto->shippingProcessingFrom,
            'shipping_processing_to' => $requestDto->shippingProcessingTo,
         ]);

         return new ShipmentListValueObject($response);
    }
}
