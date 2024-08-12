<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;

class ListExchangeDirectionsRequestDto extends ListRequestDto
{
    #[MapFrom('customer_id')]
    public ?int $customerId = null;

    #[MapFrom('given_currency_id')]
    public ?int $givenCurrencyId = null;

    #[MapFrom('with_inactive')]
    public bool $withInactive = false;

    #[MapFrom('list_type')]
    public string $listType = 'etb'; // Exchanger Telegram Bot

    #[MapFrom('telegram_bot_name')]
    public string $telegramBotName;
}