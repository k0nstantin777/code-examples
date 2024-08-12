<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\OrderCoupon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderCouponResource extends JsonResource
{
	/**
	 * @var OrderCoupon
	 */
	public $resource;

    public function toArray($request): array
	{
        return [
            'code' => $this->resource->getCode(),
           	'amount' => $this->resource->getAmount(),
        ];
    }
}
