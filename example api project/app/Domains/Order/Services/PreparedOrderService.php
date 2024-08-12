<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\DataTransferObjects\PreparedOrderDto;
use App\Domains\Order\Models\PreparedOrder;
use App\Domains\Order\Repositories\Contracts\PreparedOrderRepository;

class PreparedOrderService
{
	public function __construct(
		private readonly PreparedOrderRepository $preparedOrderRepository,
	){
	}

    public function getById(int $id) : PreparedOrder
    {
        return $this->preparedOrderRepository->findOrFail($id);
    }

	public function create(PreparedOrderDto $dto): PreparedOrder
	{
		return $this->preparedOrderRepository->create([
			'user_id' => $dto->getUserId(),
			'order' => $dto->getOrder(),
		]);
	}

    public function delete(int $id): int|bool
    {
        return $this->preparedOrderRepository->delete($id);
    }
}