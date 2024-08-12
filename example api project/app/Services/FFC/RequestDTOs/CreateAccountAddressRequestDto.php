<?php

namespace App\Services\FFC\RequestDTOs;

use Spatie\LaravelData\Data;

class CreateAccountAddressRequestDto extends Data
{
    public function __construct(
        public int $userId,
        public string $postal,
        public string $state,
        public string $address1,
        public string $city,
        public string $email,
        public string $salutation,
        public string $firstname = '',
        public string $lastname = '',
        public string $company = '',
        public string $telephone = '',
        public string $address2 = '',
    ) {
    }
}
