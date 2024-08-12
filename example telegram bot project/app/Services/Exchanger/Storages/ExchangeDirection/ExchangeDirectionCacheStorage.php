<?php

namespace App\Services\Exchanger\Storages\ExchangeDirection;

use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\Storages\CacheStorage;

class ExchangeDirectionCacheStorage implements ExchangeDirectionStorage
{
    public function __construct(protected CacheStorage $storage)
    {
    }

    public function get(string $key) : ?ExchangeDirection
    {
        return $this->storage->get($key);
    }

    public function save(string $key, ExchangeDirection $exchangeDirection): void
    {
        $this->storage->save($key, $exchangeDirection);
    }

    public function remove(string $key): void
    {
        $this->storage->forget($key);
    }
}
