<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;

class AccountAddress extends BaseValueObject
{
	protected int $id;
	protected string $postal;
	protected string $state;
	protected string $address1;
	protected string $address2;
	protected string $city;
	protected string $telephone;
	protected string $firstname;
	protected string $lastname;
	protected string $company;
	protected string $email;
	protected string $salutation;

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getPostal(): string
	{
		return $this->postal;
	}

	/**
	 * @return string
	 */
	public function getState(): string
	{
		return $this->state;
	}

	/**
	 * @return string
	 */
	public function getAddress1(): string
	{
		return $this->address1;
	}

	/**
	 * @return string
	 */
	public function getAddress2(): string
	{
		return $this->address2;
	}

	/**
	 * @return string
	 */
	public function getCity(): string
	{
		return $this->city;
	}

	/**
	 * @return string
	 */
	public function getTelephone(): string
	{
		return $this->telephone;
	}

	/**
	 * @return string
	 */
	public function getFirstname(): string
	{
		return $this->firstname;
	}

	/**
	 * @return string
	 */
	public function getLastname(): string
	{
		return $this->lastname;
	}

	/**
	 * @return string
	 */
	public function getCompany(): string
	{
		return $this->company;
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
	public function getSalutation(): string
	{
		return $this->salutation;
	}

	protected function getSchema(): array
	{
		return [
			'id',
			'postal',
			'state',
			'address1',
			'address2',
			'city',
			'telephone',
			'firstname',
			'lastname',
			'company',
			'email',
			'salutation',
		];
	}

	protected function map(): void
	{
		$this->id = $this->attributes['id'];
		$this->postal = $this->attributes['postal'] ?? '';
		$this->state = $this->attributes['state'] ?? '';
		$this->address1 = $this->attributes['address1'] ?? '';
		$this->address2 = $this->attributes['address2'] ?? '';
		$this->city = $this->attributes['city'] ?? '';
		$this->telephone = $this->attributes['telephone'] ?? '';
		$this->firstname = $this->attributes['firstname'] ?? '';
		$this->lastname = $this->attributes['lastname'] ?? '';
		$this->company = $this->attributes['company'] ?? '';
		$this->email = $this->attributes['email'] ?? '';
		$this->salutation = $this->attributes['salutation'] ?? '';
	}
}