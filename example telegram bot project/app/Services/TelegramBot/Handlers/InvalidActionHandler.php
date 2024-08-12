<?php

namespace App\Services\TelegramBot\Handlers;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class InvalidActionHandler extends AbstractHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        private readonly string $message,
        private readonly MessageSettingsService $messageSettingsService,
    ) {
        parent::__construct($telegram);
    }

    /**
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function handle(): void
    {
        $messageObj = app(ShowSimpleMessage::class);

        $text = $this->message !== '' ?
            $this->message : $this->messageSettingsService->getFormattedByCode(MessageCode::ERROR_INVALID_BOT_ACTION);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->update->getChat()->id,
        ], $messageObj($text)));
    }
}
