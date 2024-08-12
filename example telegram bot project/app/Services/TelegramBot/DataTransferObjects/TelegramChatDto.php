<?php

namespace App\Services\TelegramBot\DataTransferObjects;

use App\Services\TelegramBot\ValueObjects\Bot;
use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class TelegramChatDto extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('username')]
    public string $username;

    #[MapFrom('first_name')]
    public string $firstName;

    #[MapFrom('bot')]
    public Bot $bot;
}