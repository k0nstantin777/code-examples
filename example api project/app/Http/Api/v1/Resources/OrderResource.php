<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
	/**
	 * @var Order
	 */
	public  $resource;

	public function toArray($request) : array
    {
        return [
			'id' => $this->resource->getId(),
			'created_date' => $this->resource->getCreatedDate()->format(config('app.date_time_format')),
			'order_number' => $this->resource->getOrderNumber(),
			'status' => $this->resource->getStatus(),
			'price' => $this->resource->getPrice(),
			'tax' => $this->resource->getTax(),
			'rebate' => $this->resource->getRebate(),
			'comment' => $this->resource->getComment(),
			'delivery_address' => $this->when((bool) $this->resource->getDeliveryAddress(), function () {
				return new OrderAddressResource($this->resource->getDeliveryAddress());
			}),
			'payment_address' => $this->when((bool) $this->resource->getPaymentAddress(), function () {
				return new OrderAddressResource($this->resource->getPaymentAddress());
			}),
			'delivery_service' => $this->when((bool) $this->resource->getDeliveryService(), function () {
				return new OrderServiceResource($this->resource->getDeliveryService());
			}),
			'payment_service' => $this->when((bool) $this->resource->getPaymentService(), function () {
				return new OrderServiceResource($this->resource->getPaymentService());
			}),
			'products' => $this->when($this->resource->getProducts()->isNotEmpty(), function () {
				return OrderProductResource::collection($this->resource->getProducts());
			}),
			'grave' => $this->when((bool) $this->resource->getGrave(), function () {
				return new AccountGraveResource($this->resource->getGrave());
			}),
			'coupon' => $this->when((bool) $this->resource->getCoupon(), function () {
				return new OrderCouponResource($this->resource->getCoupon());
			}),
        ];
    }
}
