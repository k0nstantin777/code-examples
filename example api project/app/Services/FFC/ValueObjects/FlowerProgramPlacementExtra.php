<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;

class FlowerProgramPlacementExtra extends BaseValueObject
{
    private int $id;
    private Product $product;
    private string $price;
    private string $tax;

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

    /**
     * @return string|null
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getTax() : string
    {
        return $this->tax;
    }

	protected function getSchema(): array
	{
		return [
            'id',
            'product',
            'price',
            'tax',
		];
	}

    /**
     * @throws InvalidSchemaException
     */
    protected function map(): void
	{
		$this->id = $this->attributes['id'];
		$this->product = new Product($this->attributes['product']);
		$this->tax = $this->attributes['tax'];
		$this->price = $this->attributes['price'];
	}
}