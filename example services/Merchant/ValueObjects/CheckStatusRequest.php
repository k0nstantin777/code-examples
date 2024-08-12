<?php

namespace App\Services\Merchant\ValueObjects;

use App\Models\Exchange\ExchangeRequest;

class CheckStatusRequest
{
    private array $attributes;

    public function __construct(
        private ExchangeRequest $exchangeRequest
    ) {
    }

    /**
     * @return ExchangeRequest
     */
    public function getExchangeRequest(): ExchangeRequest
    {
        return $this->exchangeRequest;
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
