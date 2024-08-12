<?php

namespace App\Services\CryptoWallets;

use App\Services\BaseService;
use App\Services\CryptoWallets\Endpoints\CreateHdWallet;
use App\Services\CryptoWallets\Endpoints\DeleteHdWallet;
use App\Services\CryptoWallets\Endpoints\GenerateHdWalletAddress;
use App\Services\CryptoWallets\Endpoints\GetHdWalletAddresses;
use App\Services\CryptoWallets\Endpoints\GetHdWallets;
use App\Services\CryptoWallets\RequestDTOs\CreateHdWalletDto;
use App\Services\CryptoWallets\RequestDTOs\GenerateHdWalletAddressDto;
use App\Services\CryptoWallets\RequestDTOs\ListHdWalletsRequestDto;
use App\Services\CryptoWallets\ValueObjects\HdWallet;
use App\Services\CryptoWallets\ValueObjects\HdWalletAddress;
use App\Services\CryptoWallets\ValueObjects\HdWalletAddressCollection;
use App\Services\CryptoWallets\ValueObjects\HdWalletCollection;
use Illuminate\Support\Collection;

class CryptoWalletsApiService extends BaseService
{
    /**
     * @throws Exceptions\CryptoWalletsApiException
     */
    public function getHdWallets(ListHdWalletsRequestDto $dto): HdWalletCollection
    {
        return app(GetHdWallets::class)->execute($dto);
    }

    /**
     * @return HdWallet[]|Collection
     * @throws Exceptions\CryptoWalletsApiException
     */
    public function getAllHdWallets() : Collection|array
    {
        $listHdWalletsDto = new ListHdWalletsRequestDto();

        $results = $this->getHdWallets($listHdWalletsDto);
        $wallets = $results->getRecords();

        while ($wallets->count() < $results->getTotal()) {
            $results = $this->getHdWallets(
                new ListHdWalletsRequestDto(
                    $listHdWalletsDto->getLimit(),
                    $results->getOffset() + $listHdWalletsDto->getLimit(),
                )
            );

            $wallets = $wallets->concat($results->getRecords());
        }

        return $wallets;
    }

    /**
     * @throws Exceptions\CryptoWalletsApiException
     */
    public function getHdWalletAddresses(string $walletId, ListHdWalletsRequestDto $dto): HdWalletAddressCollection
    {
        return app(GetHdWalletAddresses::class)->execute($walletId, $dto);
    }

    /**
     * @return HdWalletAddress[]|Collection
     * @throws Exceptions\CryptoWalletsApiException
     */
    public function getAllHdWalletAddresses(string $walletId) : Collection|array
    {
        $listHdWalletsDto = new ListHdWalletsRequestDto();

        $results = $this->getHdWalletAddresses($walletId, $listHdWalletsDto);
        $walletAddresses = $results->getRecords();

        while ($walletAddresses->count() < $results->getTotal()) {
            $results = $this->getHdWalletAddresses(
                $walletId,
                new ListHdWalletsRequestDto(
                    $listHdWalletsDto->getLimit(),
                    $results->getOffset() + $listHdWalletsDto->getLimit(),
                )
            );

            $walletAddresses = $walletAddresses->concat($results->getRecords()->all());
        }

        return $walletAddresses;
    }

    /**
     * @throws Exceptions\CryptoWalletsApiException
     */
    public function generateHdWalletAddress(GenerateHdWalletAddressDto $dto) : HdWalletAddress
    {
        return app(GenerateHdWalletAddress::class)->execute($dto);
    }

    /**
     * @throws Exceptions\CryptoWalletsApiException
     */
    public function createHdWallet(CreateHdWalletDto $dto) : HdWallet
    {
        return app(CreateHdWallet::class)->execute($dto);
    }

    /**
     * @throws Exceptions\CryptoWalletsApiException
     */
    public function deleteHdWallet(string $walletId) : bool
    {
        return app(DeleteHdWallet::class)->execute($walletId);
    }
}
