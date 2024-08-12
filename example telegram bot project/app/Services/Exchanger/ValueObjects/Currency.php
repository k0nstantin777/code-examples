<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class Currency extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('position')]
    public int $position;

    #[MapFrom('label')]
    public string $label;

    #[MapFrom('label_description')]
    public string $labelDescription;

    #[MapFrom('name')]
    public string $name;

    #[MapFrom('code')]
    public string $code;

    #[MapFrom('icon')]
    public string $icon;

    #[MapFrom('reserve')]
    public string $reserve;

    #[MapFrom('exchange_prompt')]
    public string $exchangePrompt;
}