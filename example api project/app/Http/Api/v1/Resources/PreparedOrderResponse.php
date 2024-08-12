<?php

namespace App\Http\Api\v1\Resources;

use App\Domains\Order\Models\PreparedOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class PreparedOrderResponse extends JsonResource
{
	/**
	 * @var PreparedOrder
	 */
	public $resource;

	public function toArray($request) : array
    {
        return [
			'id' => $this->resource->id,
			'order' => $this->resource->order,
        ];
    }
}
