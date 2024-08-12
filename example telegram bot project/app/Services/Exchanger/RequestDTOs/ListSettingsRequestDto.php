<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ListSettingsRequestDto extends DataTransferObject
{
    #[MapFrom('codes')]
    public array $codes;
}