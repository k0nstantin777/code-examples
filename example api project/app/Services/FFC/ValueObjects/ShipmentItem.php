<?php

namespace App\Services\FFC\ValueObjects;

use Spatie\LaravelData\Data;

class ShipmentItem extends Data
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $sku,
        public string $name,
        public string $price,
        public string $tax,
        public int $quantity,
    ) {
    }
}
