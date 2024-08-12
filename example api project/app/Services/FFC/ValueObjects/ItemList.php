<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Collection;

abstract class ItemList extends BaseValueObject
{
	protected Collection $data;
	protected Meta $meta;

	/**
	 * @return Collection
	 */
	public function getData(): Collection
	{
		return $this->data;
	}

	/**
	 * @return Meta
	 */
	public function getMeta(): Meta
	{
		return $this->meta;
	}

	protected function getSchema(): array
	{
		return [
			'data',
			'meta',
		];
	}

	/**
	 * @throws InvalidSchemaException
	 */
	protected function map(): void
	{
		$this->data = $this->fillData();
		$this->meta = new Meta($this->attributes['meta']);
	}

	abstract protected function fillData() : Collection;
}