<?php

namespace App\Services\Storages;

use Illuminate\Support\Collection;

interface CollectionStorage
{
    public function get(string $key) : Collection;

    public function save(string $key, Collection $collection): void;

    public function remove(string $key) : void;
}
