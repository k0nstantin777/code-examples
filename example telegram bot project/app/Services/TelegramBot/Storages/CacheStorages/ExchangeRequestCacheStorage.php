<?php

namespace App\Services\TelegramBot\Storages\CacheStorages;

use App\Services\Storages\CacheStorage;
use App\Services\TelegramBot\Storages\ExchangeRequestStorage;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;

class ExchangeRequestCacheStorage implements ExchangeRequestStorage
{
    public function __construct(protected CacheStorage $storage)
    {
    }

    public function get(string $key) : ?ExchangeRequest
    {
        return $this->storage->get($key);
    }

    public function save(string $key, ExchangeRequest $exchangeRequest): void
    {
        $this->storage->save($key, $exchangeRequest);
    }

    public function remove(string $key): void
    {
        $this->storage->forget($key);
    }
}

