<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class Customer extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('email')]
    public string $email;

    #[MapFrom('name')]
    public string $name;
}