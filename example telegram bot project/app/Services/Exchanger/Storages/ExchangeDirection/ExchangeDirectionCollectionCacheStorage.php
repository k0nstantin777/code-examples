<?php

namespace App\Services\Exchanger\Storages\ExchangeDirection;

use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\Storages\CollectionCacheStorage;
use Illuminate\Support\Collection;

class ExchangeDirectionCollectionCacheStorage extends CollectionCacheStorage
{
    /**
     * @param string $key
     * @return Collection|ExchangeDirection[]
     */
    public function get(string $key) : Collection
    {
        return parent::get($key);
    }
}

