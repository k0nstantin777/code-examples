<?php

namespace App\Services\CryptoWallets\Endpoints;

use App\Services\CryptoWallets\Exceptions\CryptoWalletsApiException;

class DeleteHdWallet extends BaseEndpoint
{
    protected const ENDPOINT = '/api/v1/hd-wallets/:wallet_id';

    /**
     * @throws CryptoWalletsApiException
     */
    public function execute(...$arguments) : bool
    {
        $walletId = $arguments[0];

        $endpoint = str_replace(':wallet_id', $walletId, self::ENDPOINT);

        $response = $this->client->delete($endpoint);

        return (bool) ($response['success'] ?? null);
    }
}
