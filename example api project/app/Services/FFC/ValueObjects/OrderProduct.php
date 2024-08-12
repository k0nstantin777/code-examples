<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;

class OrderProduct extends BaseValueObject
{
	private int $id;
	private string $code;
	private string $name;
	private string $price;
	private int $quantity;
	private int $productId;
	private ?int $parentId;

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
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @return string|null
	 */
	public function getPrice(): ?string
	{
		return $this->price;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getQuantity(): int
	{
		return $this->quantity;
	}

	/**
	 * @return int
	 */
	public function getProductId(): int
	{
		return $this->productId;
	}

    /**
     * @return int|null
     */
    public function getParentId() : ?int
    {
        return $this->parentId;
    }

	protected function getSchema(): array
	{
		return [
			'id',
			'product_id',
			'code',
			'name',
			'price',
			'quantity',
			'?parent_id',
		];
	}

	protected function map(): void
	{
		$this->id = $this->attributes['id'];
		$this->productId = $this->attributes['product_id'];
		$this->code = $this->attributes['code'];
		$this->price = $this->attributes['price'];
		$this->name = $this->attributes['name'];
		$this->quantity = $this->attributes['quantity'];
		$this->parentId = $this->attributes['parent_id'] ?? null;
	}
}