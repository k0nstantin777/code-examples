<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowCurrentExchangeRequestMessage;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ShowCurrentExchangeRequestHandler extends ExchangeRequestProcessingHandler
{
    /**
     * @throws InvalidBotActionException
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function handle(): void
    {
        $message = app(ShowCurrentExchangeRequestMessage::class);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($this->exchangeRequest)));

        parent::handle();
    }
}
