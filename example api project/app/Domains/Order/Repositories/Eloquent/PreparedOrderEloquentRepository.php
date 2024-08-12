<?php

namespace App\Domains\Order\Repositories\Eloquent;

use App\Domains\Order\Models\PreparedOrder;
use App\Domains\Order\Repositories\Contracts\PreparedOrderRepository;
use App\Services\Repository\Eloquent\BaseEloquentRepository;

class PreparedOrderEloquentRepository extends BaseEloquentRepository implements PreparedOrderRepository
{
	protected function model(): string
	{
		return PreparedOrder::class;
	}
}