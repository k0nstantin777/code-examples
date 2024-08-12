<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
	/**
	 * @var Product
	 */
	public  $resource;

	public function toArray($request) : array
    {
        return [
            'id' => $this->resource->getId(),
            'code' => $this->resource->getCode(),
            'label' => $this->resource->getLabel(),
            'price' => $this->when($this->resource->getPrice() !== null, function () {
                return $this->resource->getPrice();
            }),
            'image' => $this->when($this->resource->getImage() !== null, function () {
				return $this->resource->getImage();
			}),
			'category' => $this->when($this->resource->getCategory() !== null, function () {
				return new CategoryResource($this->resource->getCategory());
			}),
            'stock_level' => $this->when($this->resource->getStockLevel() !== null, function () {
                return $this->resource->getStockLevel();
            }),
        ];
    }
}
