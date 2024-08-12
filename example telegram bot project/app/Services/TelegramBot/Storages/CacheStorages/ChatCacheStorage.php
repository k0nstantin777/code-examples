<?php

namespace App\Services\TelegramBot\Storages\CacheStorages;

use App\Services\Storages\CacheStorage;
use App\Services\TelegramBot\Storages\ChatStorage;
use App\Services\TelegramBot\ValueObjects\Chat;

class ChatCacheStorage implements ChatStorage
{
    public function __construct(protected CacheStorage $storage)
    {
    }

    public function get(string $key) : ?Chat
    {
        return $this->storage->get($key);
    }

    public function save(string $key, Chat $chat): void
    {
        $this->storage->save($key, $chat);
    }

    public function remove(string $key): void
    {
        $this->storage->forget($key);
    }
}

