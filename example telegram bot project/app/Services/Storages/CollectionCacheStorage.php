<?php

namespace App\Services\Storages;

use Illuminate\Support\Collection;

class CollectionCacheStorage implements CollectionStorage
{
    public function __construct(protected CacheStorage $storage)
    {
    }

    public function get(string $key) : Collection
    {
        return $this->storage->get($key) ?? collect();
    }

    public function save(string $key, Collection $collection): void
    {
        $this->storage->save($key, $collection);
    }

    public function remove(string $key): void
    {
        $this->storage->forget($key);
    }
}
