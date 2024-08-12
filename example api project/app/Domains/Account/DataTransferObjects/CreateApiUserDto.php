<?php

namespace App\Domains\Account\DataTransferObjects;

use App\Services\ValueObject\BaseValueObject;

class CreateApiUserDto extends BaseValueObject
{
	private string $name;
	private string $password;
	private string $email;
	private string $ffcId;

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function getFfcId(): string
	{
		return $this->ffcId;
	}

	protected function getSchema(): array
	{
		return [
			'email',
			'name',
			'password',
			'ffc_id'
		];
	}

	protected function map(): void
	{
		$this->name = $this->attributes['name'];
		$this->email = $this->attributes['email'];
		$this->password = $this->attributes['password'];
		$this->ffcId = $this->attributes['ffc_id'];
	}
}