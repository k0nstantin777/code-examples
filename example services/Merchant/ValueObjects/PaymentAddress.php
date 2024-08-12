<?php

namespace App\Services\Merchant\ValueObjects;

class PaymentAddress implements PaymentAddressInterface
{
    protected array $attributes;

    public function __construct(
        protected string $address,
    ) {
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function set(string $key, mixed $value) : void
    {
        $this->attributes[$key] = $value;
    }

    public function all() : array
    {
        return $this->attributes;
    }
}
