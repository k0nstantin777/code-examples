<?php

namespace App\Services\TelegramBot\Handlers;

use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Update;

abstract class AbstractHandler implements Handler
{
    protected ?Handler $nextHandler = null;
    protected Update $update;

    /**
     * @throws TelegramSDKException
     */
    public function __construct(
        protected TelegramBotApi $telegram,
    ) {
        $this->update = $this->telegram->getWebhookUpdate();
    }

    public function setNext(Handler $handler): Handler
    {
        $this->nextHandler = $handler;

        return $handler;
    }

    public function handle(): void
    {
        if ($this->nextHandler) {
            $this->nextHandler->handle();
        }
    }
}
