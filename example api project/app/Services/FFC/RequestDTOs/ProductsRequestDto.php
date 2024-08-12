<?php

namespace App\Services\FFC\RequestDTOs;

class ProductsRequestDto extends ListRequestDto
{
    public function __construct(
        public int $userId,
        public string $includes = '',
        public string $search = '',
        public bool $inStock = false,
        public ?int $categoryId = null,
        int $limit = 100,
        int $offset = 0,
        string $sort = 'id',
        string $sortDirection = 'desc'
    ) {
        parent::__construct($limit, $offset, $sort, $sortDirection);
    }
}
