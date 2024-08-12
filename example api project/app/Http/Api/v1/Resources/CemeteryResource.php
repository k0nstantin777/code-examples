<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\Cemetery;
use Illuminate\Http\Resources\Json\JsonResource;

class CemeteryResource extends JsonResource
{
    /**
     * @var Cemetery
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'city' => $this->resource->city,
            'address1' => $this->resource->address1,
            'address2' => $this->resource->address2,
            'state' => $this->resource->state,
            'stateName' => $this->resource->stateName,
            'zip' => $this->resource->zip,
            'phone' => $this->resource->phone,
            'email' => $this->resource->email,
            'is_active' => $this->resource->isActive,
        ];
    }
}
