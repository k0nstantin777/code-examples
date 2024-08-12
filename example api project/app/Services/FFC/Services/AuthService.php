<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\Authorize;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\ValueObjects\User;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;

class AuthService
{
	public function __construct(
		private readonly Authorize $authorizeEndpoint
	)
	{
	}

	/**
	 * @throws AuthenticationException|ValidationException
     */
	public function authorize(string $email, string $password) : User
	{
		try {
			return $this->authorizeEndpoint->execute($email, $password);
		} catch (JsonRpcErrorResponseException|InvalidSchemaException) {
			throw new AuthenticationException();
		}
	}
}