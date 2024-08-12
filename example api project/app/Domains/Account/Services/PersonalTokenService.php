<?php

namespace App\Domains\Account\Services;

use App\Domains\Account\DataTransferObjects\CreatePersonalTokenDto;
use App\Domains\Account\Models\ApiUser;
use JetBrains\PhpStorm\Pure;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class PersonalTokenService
{
	public function create(ApiUser $apiUser, CreatePersonalTokenDto $dto): NewAccessToken
	{
		return $apiUser->createToken($dto->getName(), $dto->getAbilities());
	}

	public function change(ApiUser $apiUser, CreatePersonalTokenDto $dto) : NewAccessToken
	{
		$this->deleteAll($apiUser);

		return $this->create($apiUser, $dto);
	}

	public function deleteCurrent(ApiUser $apiUser) : bool|int|null
	{
		$token = $this->getCurrent($apiUser);

		if (null === $token) {
			return false;
		}

		return $token->delete();
	}

	public function deleteAll(ApiUser $apiUser) : bool|int|null
	{
		return $apiUser->tokens()->delete();
	}

	#[Pure]
	public function getCurrent(ApiUser $apiUser) : ?PersonalAccessToken
	{
		return $apiUser->currentAccessToken();
	}
}