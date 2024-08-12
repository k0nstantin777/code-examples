<?php

namespace App\Services\Exchanger\Storages\TelegramBot;

use App\Services\Exchanger\ValueObjects\TelegramBotsList;
use App\Services\Storages\CacheStorage;

class TelegramBotListCacheStorage implements TelegramBotListStorage
{
    public function __construct(
        protected CacheStorage $storage,
    ) {
    }

    /**
     * @param string $key
     * @return null|TelegramBotsList
     */
    public function get(string $key) : ?TelegramBotsList
    {
        return $this->storage->get($key);
    }

    public function save(string $key, TelegramBotsList $list): void
    {
        $this->storage->save($key, $list);
    }

    public function remove(string $key): void
    {
        $this->storage->forget($key);
    }
}
