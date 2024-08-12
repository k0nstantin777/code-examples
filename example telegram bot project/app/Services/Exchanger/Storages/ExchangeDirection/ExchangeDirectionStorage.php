<?php

namespace App\Services\Exchanger\Storages\ExchangeDirection;

use App\Services\Exchanger\ValueObjects\ExchangeDirection;

interface ExchangeDirectionStorage
{
    public function get(string $key) : ?ExchangeDirection;

    public function save(string $key, ExchangeDirection $exchangeDirection): void;

    public function remove(string $key) : void;
}
