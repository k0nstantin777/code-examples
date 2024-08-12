<?php

namespace App\Services\FFC\ValueObjects;

class OrderAddress extends AccountAddress
{
	protected int|null $addressId = null;

	/**
	 * @return int|null
	 */
	public function getAddressId(): int|null
	{
		return $this->addressId;
	}

	protected function getSchema(): array
	{
		return array_merge(parent::getSchema(), [
			'address_id',
		]);
	}

	protected function map(): void
	{
		$this->addressId = $this->attributes['address_id'];

		parent::map();
	}
}