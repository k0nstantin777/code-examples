<?php

namespace App\Services\TelegramBot\Storages\CacheStorages;

use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\Storages\CacheStorage;
use App\Services\TelegramBot\Storages\ExchangeDirectionStorage;
use Illuminate\Support\Collection;

class ExchangeDirectionCacheStorage implements ExchangeDirectionStorage
{
    private const COLLECTION_KEY = 'exchange_directions_collection';

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

        $collection = $this->getAll();

        $collection = $collection->put($key, $exchangeDirection);

        $this->storage->save(self::COLLECTION_KEY, $collection);
    }

    public function remove(string $key): void
    {
        $this->storage->forget($key);

        $collection = $this->getAll();

        $collection = $collection->forget($key);

        $this->storage->save(self::COLLECTION_KEY, $collection);
    }

    /**
     * @return Collection|ExchangeDirection[]
     */
    public function getAll() : Collection
    {
        return $this->storage->get(self::COLLECTION_KEY) ?? collect();
    }
}
