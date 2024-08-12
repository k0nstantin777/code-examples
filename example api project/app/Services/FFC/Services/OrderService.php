<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\CalculateOrder;
use App\Services\FFC\Endpoints\CreateOrder;
use App\Services\FFC\Endpoints\OrderList;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CalculateOrderRequestDto;
use App\Services\FFC\RequestDTOs\CreateOrderRequestDto;
use App\Services\FFC\RequestDTOs\OrdersRequestDto;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use App\Services\FFC\ValueObjects\OrderList as OrderListValueObject;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly OrderList $orderListEndpoint,
        private readonly CalculateOrder $calculateOrderEndpoint,
        private readonly CreateOrder $createOrderEndpoint,
    ) {
    }

    /**
     * @param OrdersRequestDto $dto
     * @return OrderListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getList(OrdersRequestDto $dto): OrderListValueObject
    {
        return $this->orderListEndpoint->execute($dto);
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function calculate(CalculateOrderRequestDto $dto): array
    {
        return $this->calculateOrderEndpoint->execute($dto);
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function create(CreateOrderRequestDto $dto): array
    {
        return $this->createOrderEndpoint->execute($dto);
    }
}
