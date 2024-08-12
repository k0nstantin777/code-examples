<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class GetExchangeDirectionRequestDto extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('customer_id')]
    public ?int $customerId = null;

    #[MapFrom('with_inactive')]
    public bool $withInactive = false;
}