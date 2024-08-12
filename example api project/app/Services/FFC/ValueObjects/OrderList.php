<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Collection;

class OrderList extends ItemList
{
	/**
	 * @var Collection|Order[]
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

		foreach ($this->attributes['data'] as $orderData) {
			$results->push(new Order($orderData));
		}

		return $results;
	}
}