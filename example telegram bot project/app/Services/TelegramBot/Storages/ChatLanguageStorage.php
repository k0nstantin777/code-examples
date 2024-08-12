<?php

namespace App\Services\TelegramBot\Storages;

use App\Services\TelegramBot\ValueObjects\ChatLanguage;

interface ChatLanguageStorage
{
    public function get(string $key) : ?ChatLanguage;

    public function save(string $key, ChatLanguage $chatLanguage): void;

    public function remove(string $key) : void;
}
