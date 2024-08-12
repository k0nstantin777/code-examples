<?php

namespace App\Services\TelegramBot\Flows;

use App\Services\TelegramBot\ValueObjects\Chat;
use Telegram\Bot\Objects\Update;

abstract class Flow
{
    protected ?Chat $chat = null;

    public function getChat() : Chat
    {
        return $this->chat;
    }

    public function setChat(Chat $chat) : void
    {
        $this->chat = $chat;
    }

    abstract public function handleRequest(Update $update) : void;
}
