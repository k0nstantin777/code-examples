<?php

namespace App\Services\TelegramBot\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class Bot extends DataTransferObject
{
    #[MapFrom('name')]
    public string $name;

    #[MapFrom('username')]
    public string $username;

    #[MapFrom('token')]
    public string $token;

    #[MapFrom('webhook_url')]
    public string $webhookUrl;
}