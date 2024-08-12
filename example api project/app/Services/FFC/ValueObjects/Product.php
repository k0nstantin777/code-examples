<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;

class Product extends BaseValueObject
{
    private int $id;
    private string $code;
    private string $label;
    private ?string $price = null;
    private ?string $image = null;
    private ?string $stock_level = null;
    private ?Category $category = null;

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
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getStockLevel(): ?string
    {
        return $this->stock_level;
    }

    protected function getSchema(): array
    {
        return [
            'id',
            'code',
            'label',
            '?price',
            '?category',
            '?image',
            '?stock_level',
        ];
    }

    /**
     * @throws InvalidSchemaException
     */
    protected function map(): void
    {
        $this->id = $this->attributes['id'];
        $this->code = $this->attributes['code'];
        $this->label = $this->attributes['label'];
        $this->price = $this->attributes['price'] ?? null;
        $this->stock_level = $this->attributes['stock_level'] ?? null;
        $this->image = $this->attributes['image'] ?? null;
        $this->category = isset($this->attributes['category']) ? new Category($this->attributes['category']) :  null;
    }
}
