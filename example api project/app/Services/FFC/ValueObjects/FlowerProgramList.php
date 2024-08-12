<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Collection;

class FlowerProgramList extends ItemList
{
	/**
	 * @var Collection|FlowerProgram[]
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

		foreach ($this->attributes['data'] as $fpData) {
			$results->push(new FlowerProgram($fpData));
		}

		return $results;
	}
}