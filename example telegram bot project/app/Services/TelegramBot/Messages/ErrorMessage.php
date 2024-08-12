<?php

namespace App\Services\TelegramBot\Messages;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ErrorMessage implements SendableMessage
{
    public function __construct(
        private readonly MessageSettingsService $messageSettingsService,
    ) {
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function __invoke(...$params) : array
    {
        return [
            'text' => escapeMarkdownV2BotChars(
                $this->messageSettingsService->getFormattedByCode(MessageCode::ERROR_COMMON_ERROR_OCCURRED)
            ),
            'parse_mode' => 'MarkdownV2'
        ];
    }
}
