<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\JsonRpcClient;

abstract class BaseJsonRpcEndpoint
{
	public function __construct(protected JsonRpcClient $jsonRpcClient)
	{
	}

	abstract public function execute(...$arguments);
}