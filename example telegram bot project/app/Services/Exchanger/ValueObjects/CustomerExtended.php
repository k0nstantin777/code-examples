<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;

class CustomerExtended extends Customer
{
    #[MapFrom('balance')]
    public string $balance;

    #[MapFrom('exchange_bonus')]
    public float $exchangeBonus;

    #[MapFrom('phone')]
    public string $phone;
}