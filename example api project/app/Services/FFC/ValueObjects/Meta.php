<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;

class Meta extends BaseValueObject
{
	private int $limit;
	private int $offset;
	private int $total;

	/**
	 * @return int
	 */
	public function getLimit(): int
	{
		return $this->limit;
	}

	/**
	 * @return int
	 */
	public function getOffset(): int
	{
		return $this->offset;
	}

	/**
	 * @return int
	 */
	public function getTotal(): int
	{
		return $this->total;
	}

	protected function getSchema(): array
	{
		return [
			'limit',
			'offset',
			'total',
		];
	}

	protected function map(): void
	{
		$this->limit = $this->attributes['limit'];
		$this->offset = $this->attributes['offset'];
		$this->total = $this->attributes['total'];
	}
}