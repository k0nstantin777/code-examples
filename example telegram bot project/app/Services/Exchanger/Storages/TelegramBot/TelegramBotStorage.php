<?php

namespace App\Services\Exchanger\Storages\TelegramBot;

use App\Services\Exchanger\ValueObjects\TelegramBot;

interface TelegramBotStorage
{
    public function get(string $key) : ?TelegramBot;

    public function save(string $key, TelegramBot $telegramBot): void;

    public function remove(string $key) : void;
}
