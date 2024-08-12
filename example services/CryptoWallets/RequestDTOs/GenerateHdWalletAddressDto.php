<?php

namespace App\Services\CryptoWallets\RequestDTOs;

class GenerateHdWalletAddressDto
{
    public function __construct(
        private string $walletId,
        private string $addressPath = '',
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
    public function getAddressPath(): string
    {
        return $this->addressPath;
    }
}
