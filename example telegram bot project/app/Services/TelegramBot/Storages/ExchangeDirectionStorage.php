<?php

namespace App\Services\TelegramBot\Storages;

use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use Illuminate\Support\Collection;

interface ExchangeDirectionStorage
{
    public function get(string $key) : ?ExchangeDirection;

    public function save(string $key, ExchangeDirection $exchangeDirection): void;

    public function remove(string $key) : void;

    /**
     * @return Collection|ExchangeDirection[]
     */
    public function getAll() : Collection;
}
