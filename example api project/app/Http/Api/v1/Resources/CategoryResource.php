<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\Category;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
	/**
	 * @var Category
	 */
	public  $resource;

	/**
	 * @param $request
	 * @return array
	 */
	public function toArray($request): array
	{
        return [
            'id' => $this->resource->getId(),
            'label' => $this->resource->getLabel(),
            'code' => $this->resource->getCode(),
        ];
    }
}
