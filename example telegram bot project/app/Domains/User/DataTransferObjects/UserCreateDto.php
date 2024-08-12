<?php

namespace App\Domains\User\DataTransferObjects;

use App\Services\Language\Enums\LanguageCode;
use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class UserCreateDto extends DataTransferObject
{
    #[MapFrom('name')]
    public string $name;

    #[MapFrom('username')]
    public string $username;

    #[MapFrom('telegram_chat_id')]
    public int $telegramChatId;

    #[MapFrom('telegram_bot_name')]
    public string $telegramBotName;

    #[MapFrom('lang')]
    public LanguageCode $lang;
}