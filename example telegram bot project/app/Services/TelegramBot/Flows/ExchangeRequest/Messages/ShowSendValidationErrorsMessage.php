<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;

class ShowSendValidationErrorsMessage implements SendableMessage
{
    public function __invoke(...$params): array
    {
        /** @var ExchangeRequest $exchangeRequest */
        [$exchangeRequest] = $params;

        $text = __('Errors') . ': ' . "\n";

        $i = 1;
        foreach ($exchangeRequest->getCreationValidationErrors() as $messages) {
            foreach ($messages as $message) {
                $text .= $i . ') ' . $message . "\n";
                $i++;
            }
        }

        return [
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
    }
}
