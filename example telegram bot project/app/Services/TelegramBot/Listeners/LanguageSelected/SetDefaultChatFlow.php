<?php

namespace App\Services\TelegramBot\Listeners\LanguageSelected;

use App\Services\TelegramBot\Events\LanguageSelected;
use App\Services\TelegramBot\Flows\ExchangeRequest\ExchangeRequestProcessingFlow;
use App\Services\TelegramBot\Services\TelegramBotChatService;

class SetDefaultChatFlow
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private readonly TelegramBotChatService $telegramBotChatService,
    ) {
        //
    }

    /**
     * Handle the event.
     *
     * @param LanguageSelected $event
     * @return void
     */
    public function handle(LanguageSelected $event) : void
    {
        $chat = $this->telegramBotChatService->getByUserId($event->user->id);

        $this->telegramBotChatService->setFlow($chat, app(ExchangeRequestProcessingFlow::class));
    }
}
