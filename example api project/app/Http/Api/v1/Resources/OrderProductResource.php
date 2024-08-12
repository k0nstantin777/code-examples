<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\OrderProduct;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    /**
     * @var OrderProduct
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getId(),
            'parent_id' => $this->when($this->resource->getParentId(), $this->resource->getParentId()),
            'product_id' => $this->resource->getProductId(),
            'code' => $this->resource->getCode(),
            'name' => $this->resource->getName(),
            'price' => $this->resource->getPrice(),
            'quantity' => $this->resource->getQuantity(),
        ];
    }
}
