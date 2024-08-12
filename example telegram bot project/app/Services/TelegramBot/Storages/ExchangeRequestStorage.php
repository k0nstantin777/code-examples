<?php

namespace App\Services\TelegramBot\Storages;

use App\Services\TelegramBot\ValueObjects\ExchangeRequest;

interface ExchangeRequestStorage
{
    public function get(string $key) : ?ExchangeRequest;

    public function save(string $key, ExchangeRequest $exchangeRequest): void;

    public function remove(string $key) : void;
}
