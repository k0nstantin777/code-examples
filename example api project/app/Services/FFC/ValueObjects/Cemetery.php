<?php

namespace App\Services\FFC\ValueObjects;

use Spatie\LaravelData\Data;

class Cemetery extends Data
{
    public function __construct(
        public int $id = 0,
        public string $name = '',
        public string $zip = '',
        public string $state = '',
        public string $stateName = '',
        public string $address1 = '',
        public string $address2 = '',
        public string $city = '',
        public string $phone = '',
        public string $email = '',
        public bool $isActive = false,
    ) {
    }

    public static function makeFromData(array $data): self
    {
        return self::from([
            'id' => $data['id'] ?? 0,
            'name' => $data['name'] ?? '',
            'zip' => $data['zip'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'address1' => $data['address1'] ?? '',
            'address2' => $data['address2'] ?? '',
            'phone' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'isActive' => $data['is_active'] ?? false,
        ]);
    }
}