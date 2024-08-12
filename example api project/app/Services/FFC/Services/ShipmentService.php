<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\ShipmentList;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ShipmentsRequestDto;
use App\Services\FFC\ValueObjects\ShipmentList as ShipmentListValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public function __construct(
        private readonly ShipmentList $shipmentListEndpoint,
    ) {
    }

    /**
     * @param ShipmentsRequestDto $dto
     * @return ShipmentListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getList(ShipmentsRequestDto $dto): ShipmentListValueObject
    {
        return $this->shipmentListEndpoint->execute($dto);
    }
}
