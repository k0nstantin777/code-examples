<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class Setting extends DataTransferObject
{
    #[MapFrom('group')]
    public string $group;

    #[MapFrom('code')]
    public string $code;

    #[MapFrom('value')]
    public string $value;
}