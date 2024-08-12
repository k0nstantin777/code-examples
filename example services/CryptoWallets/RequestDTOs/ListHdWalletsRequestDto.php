<?php

namespace App\Services\CryptoWallets\RequestDTOs;

class ListHdWalletsRequestDto
{
    public function __construct(
        private int $limit = 100,
        private int $offset = 0,
        private string $sort = 'created_at',
        private string $sortDirection = 'desc',
    ) {
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @return string
     */
    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }
}
