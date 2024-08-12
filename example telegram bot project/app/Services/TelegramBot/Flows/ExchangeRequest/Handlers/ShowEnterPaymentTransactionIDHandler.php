<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ShowEnterPaymentTransactionIDHandler extends ExchangeRequestProcessingHandler
{
    /**
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $message = app(ShowSimpleMessage::class);

        $text = __('Enter transaction ID');

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($text)));

        parent::handle();
    }
}
