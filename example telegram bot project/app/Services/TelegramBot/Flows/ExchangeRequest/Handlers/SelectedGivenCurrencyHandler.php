<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectGivenCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowSelectedGivenCurrencyMessage;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SelectedGivenCurrencyHandler extends ExchangeRequestProcessingHandler
{
    /**
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function handle(): void
    {
        $awaitedButtonPressed = app(SelectGivenCurrencyButton::class);
        $data = $this->update->callbackQuery->data;

        $currencyId = $awaitedButtonPressed->getGivenCurrencyId($data);

        $this->exchangeRequest->setGivenCurrencyId($currencyId);

        $message = app(ShowSelectedGivenCurrencyMessage::class);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($currencyId)));

        parent::handle();
    }
}
