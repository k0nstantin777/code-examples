<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowResetOrCreateExchangeRequestMenuMessage;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ShowResetOrCreateExchangeRequestMenuHandler extends ExchangeRequestProcessingHandler
{
    /**
     * @throws TelegramSDKException
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $message = app(ShowResetOrCreateExchangeRequestMenuMessage::class);

        $this->telegram->sendMessage(array_merge([
                'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
            ], $message()));

        parent::handle();
    }
}
