<?php

namespace App\Domains\User\Services;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserReadService
{
    /**
     * @param string $botName
     * @param int $chatId
     * @return User
     */
    public function getByBotAndChat(string $botName, int $chatId) : User
    {
        return User::where([
            ['telegram_chat_id', $chatId],
            ['telegram_bot_name', $botName],
        ])->firstOrFail();
    }

    public function getByBotAndChatOrNull(string $botName, int $chatId) : ?User
    {
        try {
            return $this->getByBotAndChat($botName, $chatId);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return User
     */
    public function getById(int $id) : User
    {
        return User::findOrFail($id);
    }

    public function getByIdOrNull(int $id) : ?User
    {
        try {
            return $this->getById($id);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /**
     * @return Collection|User[]
     */
    public function getAllActive() : Collection
    {
        return User::where('last_active_at', '>', now()->subHours(2))->get();
    }
}