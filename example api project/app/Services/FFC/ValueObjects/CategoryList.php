<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Collection;

class CategoryList extends ItemList
{
	/**
	 * @var Collection|Category[]
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

		foreach ($this->attributes['data'] as $categoryData) {
			$results->push(new Category([
				'id' => $categoryData['id'],
				'code' => $categoryData['code'],
				'label' => $categoryData['label'],
			]));
		}

		return $results;
	}
}