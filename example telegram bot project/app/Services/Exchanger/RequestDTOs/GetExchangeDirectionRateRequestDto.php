<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class GetExchangeDirectionRateRequestDto extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('customer_id')]
    public ?int $customerId = null;

    #[MapFrom('given_sum')]
    public ?string $givenSum = null;

    #[MapFrom('received_sum')]
    public ?string $receivedSum = null;
}