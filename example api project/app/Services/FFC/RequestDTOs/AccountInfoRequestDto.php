<?php

namespace App\Services\FFC\RequestDTOs;


use App\Services\ValueObject\BaseValueObject;

class AccountInfoRequestDto extends BaseValueObject
{
	protected string $includes;
	protected int $userId;

	/**
	 * @return string
	 */
	public function getIncludes(): string
	{
		return $this->includes;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int
	{
		return $this->userId;
	}

	protected function getSchema(): array
	{
		return [
			'user_id',
			'?includes',
		];
	}

	protected function map(): void
	{
		$this->includes = $this->attributes['includes'] ?? '';
		$this->userId = $this->attributes['user_id'];
	}
}