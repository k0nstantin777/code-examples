<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\FlowerProgramMonument;
use Illuminate\Http\Resources\Json\JsonResource;

class FlowerProgramMonumentResource extends JsonResource
{
	/**
	 * @var FlowerProgramMonument
	 */
	public $resource;

    public function toArray($request): array
	{
        return [
            'monument_type' => $this->resource->getMonumentType(),
            'memorial_type' => $this->resource->getMemorialType(),
            'vase_type' => $this->resource->getVaseType(),
            'vase_size' => $this->resource->getVaseSize(),
            'vase_has_plastic' => $this->resource->getVaseHasPlastic(),
            'vase_diameter' => $this->resource->getVaseDiameter(),
        ];
    }
}
