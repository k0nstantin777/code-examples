<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateOrderRequestDto;
use Illuminate\Validation\ValidationException;

class CreateOrder extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return array
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function execute(...$arguments): array
    {
        /* @var CreateOrderRequestDto $dto */
        [$dto] = $arguments;

        return $this->jsonRpcClient->send('orders.store', [
             'user_id' => $dto->getUserId(),
             'shipping_type' => $dto->getShippingType(),
             'delivery_address_id' => $dto->getDeliveryAddressId(),
             'payment_address_id' => $dto->getPaymentAddressId(),
             'products' => $dto->getProducts(),
             'comment' => $dto->getComment(),
             'coupon' => $dto->getCoupon(),
             'grave_id' => $dto->getGraveId(),
             'ship_rate' => $dto->getShipRate(),
        ]);
    }
}
