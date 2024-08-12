<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class CreditCard extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('card_number')]
    public string $cardNumber;

    #[MapFrom('secret_card_number')]
    public string $secretCardNumber;

    #[MapFrom('status')]
    public string $status;

    #[MapFrom('status_string')]
    public string $statusString;
}