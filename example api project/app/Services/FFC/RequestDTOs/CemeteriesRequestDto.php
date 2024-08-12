<?php

namespace App\Services\FFC\RequestDTOs;

class CemeteriesRequestDto extends ListRequestDto
{
    public function __construct(
        public string $city = '',
        public string $state = '',
        public bool $isActive = true,
        int $limit = 100,
        int $offset = 0,
        string $sort = 'id',
        string $sortDirection = 'desc'
    ) {
        parent::__construct($limit, $offset, $sort, $sortDirection);
    }
}
