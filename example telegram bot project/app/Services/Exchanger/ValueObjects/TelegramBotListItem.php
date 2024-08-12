<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class TelegramBotListItem extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('name')]
    public string $name;

    #[MapFrom('telegram_name')]
    public string $telegramName;

    #[MapFrom('telegram_username')]
    public string $telegramUsername;

    #[MapFrom('telegram_token')]
    public string $telegramToken;
}