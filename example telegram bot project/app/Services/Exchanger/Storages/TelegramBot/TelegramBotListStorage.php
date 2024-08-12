<?php

namespace App\Services\Exchanger\Storages\TelegramBot;

use App\Services\Exchanger\ValueObjects\TelegramBotsList;

interface TelegramBotListStorage
{
    /**
     * @param string $key
     * @return null|TelegramBotsList
     */
    public function get(string $key) : ?TelegramBotsList;

    public function save(string $key, TelegramBotsList $list): void;

    public function remove(string $key) : void;
}
