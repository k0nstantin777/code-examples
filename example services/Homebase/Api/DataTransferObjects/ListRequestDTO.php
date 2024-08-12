<?php

namespace App\Services\Homebase\Api\DataTransferObjects;

use Spatie\LaravelData\Data;

class ListRequestDTO extends Data
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 100,
    ) {
    }
}