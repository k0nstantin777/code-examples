<?php

namespace App\Services\TelegramBot\Handlers;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Messages\ErrorMessage;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ErrorHandler extends AbstractHandler
{
    /**
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function handle(): void
    {
        $message = app(ErrorMessage::class);
        $this->telegram->sendMessage(array_merge([
                'chat_id' => $this->update->getChat()->id,
            ], $message()));
    }
}
