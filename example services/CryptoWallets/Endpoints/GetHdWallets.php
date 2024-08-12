<?php

namespace App\Services\CryptoWallets\Endpoints;

use App\Services\CryptoWallets\Exceptions\CryptoWalletsApiException;
use App\Services\CryptoWallets\RequestDTOs\ListHdWalletsRequestDto;
use App\Services\CryptoWallets\ValueObjects\HdWallet;
use App\Services\CryptoWallets\ValueObjects\HdWalletCollection;
use Illuminate\Support\Carbon;

class GetHdWallets extends BaseEndpoint
{
    protected const ENDPOINT = '/api/v1/hd-wallets';

    /**
     * @throws CryptoWalletsApiException
     */
    public function execute(...$arguments) : HdWalletCollection
    {
        /** @var ListHdWalletsRequestDto $requestDto */
        $requestDto = $arguments[0];

        $requestData = [
            'limit' => $requestDto->getLimit(),
            'sort' => $requestDto->getSort(),
            'sort_direction' => $requestDto->getSortDirection(),
            'offset' => $requestDto->getOffset(),
        ];

        $response = $this->client->get(self::ENDPOINT, $requestData);

        $wallets = collect();
        foreach ($response['data'] as $walletData) {
            $wallets->push(new HdWallet(
                $walletData['id'],
                $walletData['currency_code'],
                $walletData['extended_public_key'],
                Carbon::parse($walletData['created_at']),
            ));
        }

        return new HdWalletCollection(
            $wallets,
            $response['meta']['limit'],
            $response['meta']['offset'],
            $response['meta']['total'],
        );
    }
}
