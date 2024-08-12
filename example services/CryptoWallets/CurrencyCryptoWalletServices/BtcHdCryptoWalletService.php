<?php

namespace App\Services\CryptoWallets\CurrencyCryptoWalletServices;

use App\Services\BaseService;
use App\Services\CryptoWallets\CryptoWalletsApiService;
use App\Services\CryptoWallets\Exceptions\CryptoWalletsApiException;
use App\Services\CryptoWallets\Exceptions\CurrencyCryptoWalletException;
use App\Services\CryptoWallets\RequestDTOs\CreateHdWalletDto;
use App\Services\CryptoWallets\RequestDTOs\GenerateHdWalletAddressDto;
use App\Services\CryptoWallets\ValueObjects\HdWallet;
use App\Services\CryptoWallets\ValueObjects\HdWalletAddress;
use Illuminate\Support\Collection;

class BtcHdCryptoWalletService extends BaseService
{
    public const WALLET_CURRENCY = 'BTC';

    private ?HdWallet $wallet = null;

    public function __construct(
        private CryptoWalletsApiService $cryptoWalletsApiService,
    ) {
    }

    /**
     * @throws CurrencyCryptoWalletException
     * @throws CryptoWalletsApiException
     */
    public function getWallet() : HdWallet
    {
        if ($this->wallet) {
            return $this->wallet;
        }

        $wallets = $this->cryptoWalletsApiService->getAllHdWallets();

        foreach ($wallets as $wallet) {
            if ($wallet->getCurrencyCode() === self::WALLET_CURRENCY) {
                $this->wallet = $wallet;
                return $wallet;
            }
        }

        throw new CurrencyCryptoWalletException('HD Crypto Wallet not exist for currency ' . self::WALLET_CURRENCY);
    }

    /**
     * @return HdWallet|null
     * @throws CryptoWalletsApiException
     */
    public function getWalletOrNull() : ?HdWallet
    {
        try {
            return $this->getWallet();
        } catch (CurrencyCryptoWalletException) {
            return null;
        }
    }

    /**
     * @throws CryptoWalletsApiException
     */
    public function createWallet(string $extendPublicKey) : HdWallet
    {
        $this->wallet = $this->cryptoWalletsApiService->createHdWallet(
            new CreateHdWalletDto(
                $extendPublicKey,
                self::WALLET_CURRENCY,
            )
        );

        return $this->wallet;
    }

    /**
     * @throws CryptoWalletsApiException
     */
    public function deleteWallet(string $id) : bool
    {
        $result = $this->cryptoWalletsApiService->deleteHdWallet($id);

        $this->wallet = null;

        return $result;
    }

    /**
     * @return HdWalletAddress[]|Collection
     * @throws CryptoWalletsApiException
     * @throws CurrencyCryptoWalletException
     */
    public function getAddresses(): array|Collection
    {
        return $this->cryptoWalletsApiService->getAllHdWalletAddresses($this->getWallet()->getId());
    }

    /**
     * @throws CurrencyCryptoWalletException
     * @throws CryptoWalletsApiException
     */
    public function generateAddress(string $path = '') : HdWalletAddress
    {
        return $this->cryptoWalletsApiService->generateHdWalletAddress(
            new GenerateHdWalletAddressDto(
                $this->getWallet()->getId(),
                $path,
            )
        );
    }
}
