<?php

namespace App\Services\CryptoWallets\Endpoints;

use App\Services\CryptoWallets\Client;

abstract class BaseEndpoint
{
    public function __construct(protected Client $client)
    {
    }

    abstract public function execute(...$arguments);
}
