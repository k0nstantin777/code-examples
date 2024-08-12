<?php

namespace App\Http\Api\v1\PrivateApi\Resources;

use App\Services\FFC\ValueObjects\ShipmentItem;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentItemResource extends JsonResource
{
    /**
     * @var ShipmentItem
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'product_id' => $this->resource->productId,
            'sku' => $this->resource->sku,
            'name' => $this->resource->name,
            'price' => $this->resource->price,
            'tax' => $this->resource->tax,
            'quantity' => $this->resource->quantity,
        ];
    }
}
