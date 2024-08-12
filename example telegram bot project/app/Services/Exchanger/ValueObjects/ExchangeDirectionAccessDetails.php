<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ExchangeDirectionAccessDetails extends DataTransferObject
{
    #[MapFrom('is_allowed')]
    public bool $isAllowed;

    #[MapFrom('cause')]
    public string $cause;
}