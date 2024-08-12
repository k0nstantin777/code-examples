<?php

namespace App\Services\CryptoWallets\ValueObjects;

use Illuminate\Support\Carbon;

class HdWalletAddress
{
    public function __construct(
        private string $walletId,
        private string $address,
        private string $path,
        private Carbon $createdAt,
    ) {
    }

    /**
     * @return string
     */
    public function getWalletId(): string
    {
        return $this->walletId;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
}
