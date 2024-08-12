<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class CreateExchangeRequestRequestDto extends DataTransferObject
{
    #[MapFrom('customer_id')]
    public ?int $customerId = null;

    #[MapFrom('customer_email')]
    public string $customerEmail;

    #[MapFrom('exchange_direction_id')]
    public string $exchangeDirectionId;

    #[MapFrom('given_sum')]
    public string $givenSum;

    #[MapFrom('received_sum')]
    public string $receivedSum;

    #[MapFrom('commission')]
    public string $commission;

    #[MapFrom('attributes')]
    public array $attributes;
}