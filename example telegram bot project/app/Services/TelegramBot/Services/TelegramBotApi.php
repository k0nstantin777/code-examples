<?php

namespace App\Services\TelegramBot\Services;

use App\Services\TelegramBot\ValueObjects\Bot;
use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class TelegramBotApi
{
    public function __construct(
        private Bot $bot
    ) {
    }

    /**
     * @throws TelegramSDKException
     */
    public function sendMessage(array $message) : Message
    {
        return $this->getApi()->sendMessage($message);
    }

    /**
     * @throws TelegramSDKException
     */
    public function getWebhookUpdate() : Update
    {
        return $this->getApi()->getWebhookUpdate();
    }

    /**
     * @throws TelegramSDKException
     */
    public function handleCommand() : array|Update
    {
        return $this->getApi()->commandsHandler(true);
    }

    public function setBot(Bot $bot) : self
    {
        $this->bot = $bot;

        return $this;
    }

    public function getBot() : Bot
    {
        return $this->bot;
    }

    /**
     * @throws TelegramSDKException
     */
    private function getApi() : Api
    {
        return app(BotsManager::class)->bot($this->bot->name);
    }
}
