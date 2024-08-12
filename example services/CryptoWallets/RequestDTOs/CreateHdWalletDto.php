<?php

namespace App\Services\CryptoWallets\RequestDTOs;

class CreateHdWalletDto
{
    public function __construct(
        private string $extendedPublicKey,
        private string $currencyCode,
    ) {
    }

    /**
     * @return string
     */
    public function getExtendedPublicKey(): string
    {
        return $this->extendedPublicKey;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
}
