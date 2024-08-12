<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\UpdateProductRequestDto;
use Illuminate\Validation\ValidationException;

class UpdateProduct extends BaseJsonRpcEndpoint
{
	/**
	 * @param mixed ...$arguments
	 * @return string
     * @throws JsonRpcErrorResponseException|ValidationException
     */
	public function execute(...$arguments): string
	{
		/* @var UpdateProductRequestDto $dto */
		[$dto] = $arguments;

        return $this->jsonRpcClient->send('products.update', [
             'code' => $dto->getCode(),
             'stock_level' => $dto->getStockLevel(),
		]);
	}
}
