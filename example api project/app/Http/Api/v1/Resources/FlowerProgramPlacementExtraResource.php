<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\FlowerProgramPlacementExtra;
use Illuminate\Http\Resources\Json\JsonResource;

class FlowerProgramPlacementExtraResource extends JsonResource
{
	/**
	 * @var FlowerProgramPlacementExtra
	 */
	public $resource;

    public function toArray($request): array
	{
        return [
            'id' => $this->resource->getId(),
            'product' => new ProductResource($this->resource->getProduct()),
            'price' => $this->resource->getPrice(),
            'tax' => $this->resource->getTax(),
        ];
    }
}
