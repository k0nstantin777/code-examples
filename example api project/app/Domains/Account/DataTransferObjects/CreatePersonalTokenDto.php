<?php

namespace App\Domains\Account\DataTransferObjects;

use App\Services\ValueObject\BaseValueObject;

class CreatePersonalTokenDto extends BaseValueObject
{
	private string $name;
	private array $abilities = [];

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getAbilities(): array
	{
		return $this->abilities;
	}

	protected function getSchema(): array
	{
		return [
			'name',
			'abilities',
		];
	}

	protected function map(): void
	{
		$this->name = $this->attributes['name'];
		$this->abilities = $this->attributes['abilities'];
	}
}