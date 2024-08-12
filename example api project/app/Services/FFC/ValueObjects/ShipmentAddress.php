<?php

namespace App\Services\FFC\ValueObjects;

use Spatie\LaravelData\Data;

class ShipmentAddress extends Data
{
    public function __construct(
        public int $id,
        public string $type,
        public string $postal,
        public string $state,
        public string $address1,
        public string $city,
        public string $name,
        public string $company,
        public string $address2 = '',
        public string $country = 'US',
        public ?int $cemeteryId = null,
    ) {
    }
}
