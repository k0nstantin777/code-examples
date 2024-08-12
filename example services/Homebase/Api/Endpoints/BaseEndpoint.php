<?php

namespace App\Services\Homebase\Api\Endpoints;

use App\Services\Homebase\Api\Client;

abstract class BaseEndpoint
{
    public function __construct(protected Client $client)
    {
    }

    abstract public function execute(...$arguments);
}
