<?php

namespace App\Services\TelegramBot\Storages\CacheStorages;

use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\Storages\CacheStorage;
use App\Services\TelegramBot\Storages\ActiveExchangeRequestStorage;
use Illuminate\Support\Collection;

class ActiveExchangeRequestCacheStorage implements ActiveExchangeRequestStorage
{
    private const COLLECTION_KEY = 'exchange_requests_collection';

    public function __construct(protected CacheStorage $storage)
    {
    }

    public function get(string $key) : ?ActiveExchangeRequest
    {
        return $this->storage->get($key);
    }

    public function save(string $key, ActiveExchangeRequest $exchangeRequest): void
    {
        $this->storage->save($key, $exchangeRequest);

        $collection = $this->getAll();

        $collection = $collection->put($key, $exchangeRequest);

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
     * @return Collection|ActiveExchangeRequest[]
     */
    public function getAll() : Collection
    {
        return $this->storage->get(self::COLLECTION_KEY) ?? collect();
    }
}
