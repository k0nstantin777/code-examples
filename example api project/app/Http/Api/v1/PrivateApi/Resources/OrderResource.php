<?php

namespace App\Http\Api\v1\PrivateApi\Resources;

use App\Services\FFC\ValueObjects\Order;
use App\Http\Api\v1\Resources\OrderResource as BaseOrderResource;

class OrderResource extends BaseOrderResource
{
    /**
     * @var Order
     */
    public $resource;

    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'customer' => new OrderCustomerResource($this->resource->getCustomer())
        ]);
    }
}
