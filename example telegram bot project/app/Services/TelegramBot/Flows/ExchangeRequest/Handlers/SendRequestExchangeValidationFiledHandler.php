<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowCreateNewExchangeRequestMessage;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowSendValidationErrorsMessage;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SendRequestExchangeValidationFiledHandler extends ExchangeRequestProcessingHandler
{
    /**
     * @throws TelegramSDKException
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $message = app(ShowSendValidationErrorsMessage::class);

        $this->telegram->sendMessage(array_merge([
                'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
            ], $message($this->exchangeRequest)));

        $message = app(ShowCreateNewExchangeRequestMessage::class);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($this->exchangeRequest)));

        parent::handle();
    }
}
