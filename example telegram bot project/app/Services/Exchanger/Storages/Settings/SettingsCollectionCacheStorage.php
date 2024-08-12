<?php

namespace App\Services\Exchanger\Storages\Settings;

use App\Services\Storages\CacheStorage;
use Illuminate\Support\Collection;

class SettingsCollectionCacheStorage implements SettingsCollectionStorage
{
    public function __construct(protected CacheStorage $storage)
    {
    }

    public function get(string $key) : ?Collection
    {
        return $this->storage->get($key);
    }

    public function save(string $key, Collection $settings): void
    {
        $this->storage->save($key, $settings);
    }

    public function remove(string $key): void
    {
        $this->storage->forget($key);
    }
}
