<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowGivenCurrenciesMessage;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ShowSelectGivenCurrencyMenuHandler extends ExchangeRequestProcessingHandler
{
    /**
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties|BindingResolutionException
     */
    public function handle(): void
    {
        $message = app(ShowGivenCurrenciesMessage::class);

        $this->telegram->sendMessage(array_merge([
                'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
            ], $message($this->exchangeRequest)));

        parent::handle();
    }
}
