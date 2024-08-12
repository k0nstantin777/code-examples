<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class GetCustomerRequestDto extends DataTransferObject
{
    #[MapFrom('id')]
    public ?int $id;

    #[MapFrom('email')]
    public ?string $email;
}