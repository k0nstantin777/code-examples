<?php

namespace App\Domains\Account\Services;

use App\Domains\Account\DataTransferObjects\CreateApiUserDto;
use App\Domains\Account\Models\ApiUser;
use App\Domains\Account\Repositories\Contracts\ApiUserRepository;

class ApiUserService
{
	public function __construct(
		private ApiUserRepository $apiUserRepository,
	){
	}

	public function create(CreateApiUserDto $dto): ApiUser
	{
		return $this->apiUserRepository->create([
			'email' => $dto->getEmail(),
			'name' => $dto->getName(),
			'password' => $dto->getPassword(),
			'ffc_id' => $dto->getFfcId(),
		]);
	}
}