<?php

namespace App\Services\TelegramBot\Messages;

class ShowSimpleMessage implements SendableMessage
{
    public function __invoke(...$params): array
    {
        return [
            'text' => $params[0],
            'parse_mode' => 'MarkdownV2'
        ];
    }
}
