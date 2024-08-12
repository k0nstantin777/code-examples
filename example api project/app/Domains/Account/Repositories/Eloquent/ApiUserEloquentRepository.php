<?php

namespace App\Domains\Account\Repositories\Eloquent;

use App\Domains\Account\Models\ApiUser;
use App\Domains\Account\Repositories\Contracts\ApiUserRepository;
use App\Services\Repository\Eloquent\BaseEloquentRepository;

class ApiUserEloquentRepository extends BaseEloquentRepository implements ApiUserRepository
{
	protected function model(): string
	{
		return ApiUser::class;
	}
}