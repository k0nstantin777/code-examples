<?php

namespace App\Services\CryptoWallets\Endpoints;

use App\Services\CryptoWallets\Exceptions\CryptoWalletsApiException;
use App\Services\CryptoWallets\RequestDTOs\ListHdWalletsRequestDto;
use App\Services\CryptoWallets\ValueObjects\HdWalletAddress;
use App\Services\CryptoWallets\ValueObjects\HdWalletAddressCollection;
use Illuminate\Support\Carbon;

class GetHdWalletAddresses extends BaseEndpoint
{
    protected const ENDPOINT = '/api/v1/hd-wallet-addresses/:wallet_id';

    /**
     * @throws CryptoWalletsApiException
     */
    public function execute(...$arguments) : HdWalletAddressCollection
    {
        /** @var ListHdWalletsRequestDto $requestDto */
        [$walletId, $requestDto] = $arguments;

        $requestData = [
            'limit' => $requestDto->getLimit(),
            'sort' => $requestDto->getSort(),
            'sort_direction' => $requestDto->getSortDirection(),
            'offset' => $requestDto->getOffset(),
        ];

        $endpoint = str_replace(':wallet_id', $walletId, self::ENDPOINT);

        $response = $this->client->get($endpoint, $requestData);

        $walletAddresses = collect();
        foreach ($response['data'] as $walletData) {
            $walletAddresses->push(new HdWalletAddress(
                $walletData['wallet_id'],
                $walletData['address'],
                $walletData['path'],
                Carbon::parse($walletData['created_at']),
            ));
        }

        return new HdWalletAddressCollection(
            $walletAddresses,
            $response['meta']['limit'],
            $response['meta']['offset'],
            $response['meta']['total'],
        );
    }
}
