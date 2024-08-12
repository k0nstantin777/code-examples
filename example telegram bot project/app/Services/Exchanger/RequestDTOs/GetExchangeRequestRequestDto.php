<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class GetExchangeRequestRequestDto extends DataTransferObject
{
    #[MapFrom('id')]
    public string $id;

    #[MapFrom('customer_id')]
    public int $customerId;
}