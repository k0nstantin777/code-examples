<?php

namespace App\Services\FFC\RequestDTOs;


use App\Services\ValueObject\BaseValueObject;

class UpdateProductRequestDto extends BaseValueObject
{
	protected string $code;
	protected ?int $stockLevel;

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @return ?int
	 */
	public function getStockLevel(): ?int
	{
		return $this->stockLevel;
	}

	protected function getSchema(): array
	{
		return [
			'code',
			'?stock_level',
		];
	}

	protected function map(): void
	{
		$this->code = $this->attributes['code'];
		$this->stockLevel = $this->attributes['stock_level'] ?? null;
	}
}
