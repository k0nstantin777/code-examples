<?php

namespace App\Http\Api\v1\PrivateApi\Resources;

use App\Services\FFC\ValueObjects\ShipmentAddress;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentAddressResource extends JsonResource
{
    /**
     * @var ShipmentAddress
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => $this->resource->type,
            'postal' => $this->resource->postal,
            'state' => $this->resource->state,
            'address1' => $this->resource->address1,
            'address2' => $this->resource->address2 ?? '',
            'city' => $this->resource->city,
            'name' => $this->resource->name,
            'company' => $this->resource->company ?? '',
            'country' => $this->resource->country,
            'cemetery_id' => $this->resource->cemeteryId,
        ];
    }
}
