<?php

namespace App\Services\Exchanger\Storages\TelegramBot;

use App\Services\Exchanger\ValueObjects\TelegramBot;
use App\Services\Storages\CacheStorage;

class TelegramBotCacheStorage implements TelegramBotStorage
{
    public function __construct(protected CacheStorage $storage)
    {
    }

    public function get(string $key) : ?TelegramBot
    {
        return $this->storage->get($key);
    }

    public function save(string $key, TelegramBot $telegramBot): void
    {
        $this->storage->save($key, $telegramBot);
    }

    public function remove(string $key): void
    {
        $this->storage->forget($key);
    }
}
