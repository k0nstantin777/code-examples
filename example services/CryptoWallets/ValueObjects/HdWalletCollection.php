<?php

namespace App\Services\CryptoWallets\ValueObjects;

use Illuminate\Support\Collection;

class HdWalletCollection
{
    public function __construct(
        private Collection $records,
        private int $limit,
        private int $offset,
        private int $total,
    ) {
    }

    /**
     * @return HdWallet[]|Collection
     */
    public function getRecords(): array|Collection
    {
        return $this->records;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
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
    public function getTotal(): int
    {
        return $this->total;
    }
}
