<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ShowEnterEmailOrLoginHandler extends ExchangeRequestProcessingHandler
{
    /**
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $message = app(ShowSimpleMessage::class);

        $text = __('Enter your email or run /login command for sign in');

        $this->telegram->sendMessage(array_merge([
                'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
            ], $message($text)));

        parent::handle();
    }
}
