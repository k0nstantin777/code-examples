<?php

namespace App\Services\TelegramBot\Listeners\ChatLogout;

use App\Services\TelegramBot\Events\ChatLogout;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveExchangeRequest implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
    ) {
        //
    }

    /**
     * Handle the event.
     *
     * @param ChatLogout $event
     * @return void
     */
    public function handle(ChatLogout $event) : void
    {
        $exchangeRequest = $this->telegramBotExchangeRequestService->getByUserId($event->user->id);

        if (!$exchangeRequest) {
            return;
        }

        $this->telegramBotExchangeRequestService->delete($exchangeRequest);
    }
}
