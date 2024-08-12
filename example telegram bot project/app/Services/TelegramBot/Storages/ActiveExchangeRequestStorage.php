<?php

namespace App\Services\TelegramBot\Storages;

use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use Illuminate\Support\Collection;

interface ActiveExchangeRequestStorage
{
    public function get(string $key) : ?ActiveExchangeRequest;

    public function save(string $key, ActiveExchangeRequest $exchangeRequest): void;

    public function remove(string $key) : void;

    /**
     * @return Collection|ActiveExchangeRequest[]
     */
    public function getAll() : Collection;
}
