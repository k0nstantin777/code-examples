<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class TelegramBotAttribute extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('name')]
    public string $name;

    #[MapFrom('code')]
    public string $code;

    #[MapFrom('value')]
    public string|array $value;
}