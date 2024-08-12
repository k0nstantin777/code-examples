<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\ValueObjects\User;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Validation\ValidationException;

class Authorize extends BaseJsonRpcEndpoint
{
	/**
	 * @param mixed ...$arguments
	 * @return User
	 * @throws InvalidSchemaException
	 * @throws JsonRpcErrorResponseException|ValidationException
     */
	public function execute(...$arguments) : User
	{
		[$email, $password] = $arguments;

		 $response = $this->jsonRpcClient->send('auth', [
			'email' => $email,
			'password' => $password,
		]);

		 return new User($response);
	}
}