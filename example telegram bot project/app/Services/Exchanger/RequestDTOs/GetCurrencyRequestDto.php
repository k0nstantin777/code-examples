<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class GetCurrencyRequestDto extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;
}