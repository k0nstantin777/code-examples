<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ExchangeFormAttribute extends DataTransferObject
{
    public const REQUISITES_GIVEN_CURRENCY_CODE = 'requisites_given_currency';
    public const REQUISITES_RECEIVED_CURRENCY_CODE = 'requisites_received_currency';

    #[MapFrom('id')]
    public int $id;

    #[MapFrom('name')]
    public string $name;

    #[MapFrom('code')]
    public string $code;

    #[MapFrom('value')]
    public string $value;
}