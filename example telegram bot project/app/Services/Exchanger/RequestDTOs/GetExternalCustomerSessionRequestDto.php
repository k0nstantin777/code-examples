<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class GetExternalCustomerSessionRequestDto extends DataTransferObject
{
    #[MapFrom('type')]
    public string $type;

    #[MapFrom('params')]
    public array $params;
}