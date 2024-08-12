<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\JsonRpcClient;

abstract class BaseJsonRpcEndpoint
{
    public function __construct(protected readonly JsonRpcClient $jsonRpcClient)
    {
    }

    abstract public function execute(...$arguments);
}
