<?php

namespace App\Services\FFC\RequestDTOs;

use Spatie\LaravelData\Data;

class ListRequestDto extends Data
{
    public function __construct(
        public int $limit = 100,
        public int $offset = 0,
        public string $sort = 'id',
        public string $sortDirection = 'desc',
    ) {
    }
}
