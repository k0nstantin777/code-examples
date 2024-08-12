<?php

namespace App\Services\TelegramBot\Storages;

use App\Services\TelegramBot\ValueObjects\Chat;

interface ChatStorage
{
    public function get(string $key) : ?Chat;

    public function save(string $key, Chat $chat): void;

    public function remove(string $key) : void;
}
