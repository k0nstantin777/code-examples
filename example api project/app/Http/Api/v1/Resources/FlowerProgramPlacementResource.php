<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\FlowerProgramPlacement;
use Illuminate\Http\Resources\Json\JsonResource;

class FlowerProgramPlacementResource extends JsonResource
{
	/**
	 * @var FlowerProgramPlacement
	 */
	public $resource;

    public function toArray($request): array
	{
        return [
            'id' => $this->resource->getId(),
            'product' => new ProductResource($this->resource->getProduct()),
            'price' => $this->resource->getPrice(),
            'ready_date' => $this->resource->getReadyDate()?->format(config('app.date_format')),
            'placement_date' => $this->resource->getPlacementDate()->format(config('app.date_format')),
            'extras' => $this->when($this->resource->getExtras()->isNotEmpty(), function () {
                return FlowerProgramPlacementExtraResource::collection($this->resource->getExtras());
            }),
            'tax' => $this->resource->getTax(),
        ];
    }
}
