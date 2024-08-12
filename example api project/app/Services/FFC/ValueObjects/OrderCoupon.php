<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;

class OrderCoupon extends BaseValueObject
{
	private string $code;
	private int $amount;

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @return int
	 */
	public function getAmount(): int
	{
		return $this->amount;
	}

	protected function getSchema(): array
	{
		return [
			'code',
			'amount',
		];
	}

	protected function map(): void
	{
		$this->code = $this->attributes['code'];
		$this->amount = $this->attributes['amount'];
	}
}