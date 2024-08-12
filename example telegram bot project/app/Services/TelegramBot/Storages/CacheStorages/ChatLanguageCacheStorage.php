<?php

namespace App\Services\TelegramBot\Storages\CacheStorages;

use App\Services\Storages\CacheStorage;
use App\Services\TelegramBot\Storages\ChatLanguageStorage;
use App\Services\TelegramBot\ValueObjects\ChatLanguage;

class ChatLanguageCacheStorage implements ChatLanguageStorage
{
    public function __construct(protected CacheStorage $storage)
    {
    }

    public function get(string $key) : ?ChatLanguage
    {
        return $this->storage->get($key);
    }

    public function save(string $key, ChatLanguage $chatLanguage): void
    {
        $this->storage->save($key, $chatLanguage);
    }

    public function remove(string $key): void
    {
        $this->storage->forget($key);
    }
}

