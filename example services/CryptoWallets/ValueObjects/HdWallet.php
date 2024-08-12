<?php

namespace App\Services\CryptoWallets\ValueObjects;

use Illuminate\Support\Carbon;

class HdWallet
{
    public function __construct(
        private string $id,
        private string $currencyCode,
        private string $extendedPublicKey,
        private Carbon $createdAt,
    ) {
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @return string
     */
    public function getExtendedPublicKey(): string
    {
        return $this->extendedPublicKey;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
}
