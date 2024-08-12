<?php

namespace App\Services\CryptoWallets\Endpoints;

use App\Services\CryptoWallets\Exceptions\CryptoWalletsApiException;
use App\Services\CryptoWallets\RequestDTOs\GenerateHdWalletAddressDto;
use App\Services\CryptoWallets\ValueObjects\HdWalletAddress;
use Illuminate\Support\Carbon;

class GenerateHdWalletAddress extends BaseEndpoint
{
    protected const ENDPOINT = '/api/v1/hd-wallet-addresses/:wallet_id/generate';

    /**
     * @throws CryptoWalletsApiException
     */
    public function execute(...$arguments) : HdWalletAddress
    {
        /** @var GenerateHdWalletAddressDto $requestDto */
        $requestDto = $arguments[0];

        $endpoint = str_replace(':wallet_id', $requestDto->getWalletId(), self::ENDPOINT);

        $requestData = array_filter([
            'path' => $requestDto->getAddressPath(),
        ]);

        $response = $this->client->post($endpoint, $requestData);

        return new HdWalletAddress(
            $response['data']['wallet_id'],
            $response['data']['address'],
            $response['data']['path'],
            Carbon::parse($response['data']['created_at']),
        );
    }
}
