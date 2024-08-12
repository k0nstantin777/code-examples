<?php

namespace App\Services\CryptoWallets\Endpoints;

use App\Services\CryptoWallets\Exceptions\CryptoWalletsApiException;
use App\Services\CryptoWallets\RequestDTOs\CreateHdWalletDto;
use App\Services\CryptoWallets\ValueObjects\HdWallet;
use Illuminate\Support\Carbon;

class CreateHdWallet extends BaseEndpoint
{
    protected const ENDPOINT = '/api/v1/hd-wallets';

    /**
     * @throws CryptoWalletsApiException
     */
    public function execute(...$arguments) : HdWallet
    {
        /** @var CreateHdWalletDto $requestDto */
        $requestDto = $arguments[0];

        $requestData = array_filter([
            'extended_public_key' => $requestDto->getExtendedPublicKey(),
            'currency_code' => $requestDto->getCurrencyCode(),
        ]);

        $response = $this->client->post(self::ENDPOINT, $requestData);

        return new HdWallet(
            $response['data']['id'],
            $response['data']['currency_code'],
            $response['data']['extended_public_key'],
            Carbon::parse($response['data']['created_at']),
        );
    }
}
