<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ExchangeDirectionRate extends DataTransferObject
{
    #[MapFrom('given_sum')]
    public string $givenSum;

    #[MapFrom('received_sum')]
    public string $receivedSum;

    #[MapFrom('commission')]
    public string $commission;
}