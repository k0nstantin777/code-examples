<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\OrdersRequestDto;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use App\Services\FFC\ValueObjects\OrderList as OrderListValueObject;
use Illuminate\Validation\ValidationException;

class OrderList extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return OrderListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function execute(...$arguments): OrderListValueObject
    {
        /* @var OrdersRequestDto $orderRequestDto */
        [$orderRequestDto] = $arguments;

         $response = $this->jsonRpcClient->send('orders', [
            'sort' => $orderRequestDto->sort,
            'limit' => $orderRequestDto->limit,
            'offset' => $orderRequestDto->offset,
            'sort_direction' => $orderRequestDto->sortDirection,
            'includes' => $orderRequestDto->includes,
            'status' => $orderRequestDto->status,
            'search' => $orderRequestDto->search,
            'user_id' => $orderRequestDto->userId,
            'from' => $orderRequestDto->from,
            'to' => $orderRequestDto->to,
         ]);

         return new OrderListValueObject($response);
    }
}
