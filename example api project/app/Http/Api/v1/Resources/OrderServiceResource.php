<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\OrderService;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderServiceResource extends JsonResource
{
	/**
	 * @var OrderService
	 */
	public $resource;

    public function toArray($request): array
	{
        return [
            'id' => $this->resource->getId(),
            'name' => $this->resource->getName(),
            'price' => $this->resource->getPrice(),
            'tax' => $this->resource->getTax(),
			'service_id' => $this->resource->getServiceId(),
        ];
    }
}
