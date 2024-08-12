<?php

namespace App\Services\TelegramBot\Services;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Flows\Flow;
use App\Services\TelegramBot\Storages\ChatStorage;
use App\Services\TelegramBot\ValueObjects\Chat;

class TelegramBotChatService
{
    public function __construct(
        private readonly ChatStorage $chatStorage,
    ) {
    }

    public function getByUserId(int $userId) : ?Chat
    {
        return $this->chatStorage->get($userId);
    }

    public function save(Chat $chat) : void
    {
        $this->chatStorage->save($chat->getUser()->id, $chat);
    }

    public function delete(Chat $chat) : void
    {
        $this->chatStorage->remove($chat->getUser()->id);
    }

    public function resetForUser(User $user) : void
    {
        $this->save($this->createNewForUser($user));
    }

    public function createNewForUser(User $user) : Chat
    {
        return new Chat($user);
    }

    public function setFlow(Chat $chat, Flow $flow) : void
    {
        $chat->changeFlow($flow);
        $this->save($chat);
    }
}
