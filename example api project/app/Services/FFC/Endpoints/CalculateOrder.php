<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CalculateOrderRequestDto;
use Illuminate\Validation\ValidationException;

class CalculateOrder extends BaseJsonRpcEndpoint
{
	/**
	 * @param mixed ...$arguments
	 * @return array
     * @throws JsonRpcErrorResponseException|ValidationException
     */
	public function execute(...$arguments) : array
	{
		/* @var CalculateOrderRequestDto $calculateOrderRequest */
		[$calculateOrderRequest] = $arguments;

        return $this->jsonRpcClient->send('calculate-order', [
             'user_id' => $calculateOrderRequest->getUserId(),
             'shipping_type' => $calculateOrderRequest->getShippingType(),
             'delivery_address_id' => $calculateOrderRequest->getDeliveryAddressId(),
             'payment_address_id' => $calculateOrderRequest->getPaymentAddressId(),
             'products' => $calculateOrderRequest->getProducts(),
             'comment' => $calculateOrderRequest->getComment(),
             'coupon' => $calculateOrderRequest->getCoupon(),
             'grave_id' => $calculateOrderRequest->getGraveId(),
		]);
	}
}