<?php

namespace App\Services\TelegramBot\Listeners\ChatLogout;

use App\Services\TelegramBot\Events\ChatLogout;
use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ShowLogoutMessage implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private readonly TelegramBotApi $api,
    ) {
        //
    }

    /**
     * Handle the event.
     *
     * @param ChatLogout $event
     * @return void
     * @throws TelegramSDKException
     */
    public function handle(ChatLogout $event) : void
    {
        $message = app(ShowSimpleMessage::class);
        $text = 'Your session is closed and all entered data has been deleted';

        $this->api->sendMessage(array_merge([
            'chat_id' => $event->user->telegram_chat_id,
        ], $message($text)));
    }
}
