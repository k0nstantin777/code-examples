<?php

namespace App\Services\Exchanger\Storages\Settings;

use App\Services\Exchanger\ValueObjects\Setting;
use Illuminate\Support\Collection;

interface SettingsCollectionStorage
{
    /**
     * @param string $key
     * @return Collection|Setting[]
     */
    public function get(string $key) : ?Collection;

    public function save(string $key, Collection $settings): void;

    public function remove(string $key) : void;
}
