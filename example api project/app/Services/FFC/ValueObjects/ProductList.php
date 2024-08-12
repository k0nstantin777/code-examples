<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Collection;

class ProductList extends ItemList
{
	/**
	 * @var Collection|Product[]
	 */
	protected Collection $data;

	/**
	 * @throws InvalidSchemaException
	 */
	protected function fillData() : Collection
	{
		$results = collect();

		if(empty($this->attributes['data'])) {
			return $results;
		}

		foreach ($this->attributes['data'] as $productData) {
			$results->push(new Product($productData));
		}

		return $results;
	}
}