<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;

class OrderService extends BaseValueObject
{
	private int $id;
	private string $name;
	private string $price;
	private string $tax;
	private int $serviceId;

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
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPrice(): string
	{
		return $this->price;
	}

    public function getTax(): string
    {
        return $this->tax;
    }

	/**
	 * @return int
	 */
	public function getServiceId(): int
	{
		return $this->serviceId;
	}

	protected function getSchema(): array
	{
		return [
			'id',
			'name',
			'price',
			'tax',
			'service_id',
		];
	}

	protected function map(): void
	{
		$this->id = $this->attributes['id'];
		$this->name = $this->attributes['name'];
		$this->price = $this->attributes['price'];
		$this->tax = $this->attributes['tax'];
		$this->serviceId = $this->attributes['service_id'];
	}
}