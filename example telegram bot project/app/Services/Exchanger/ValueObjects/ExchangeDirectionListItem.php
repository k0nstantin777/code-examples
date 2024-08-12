<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ExchangeDirectionListItem extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('given_currency')]
    public Currency $givenCurrency;

    #[MapFrom('received_currency')]
    public Currency $receivedCurrency;
}