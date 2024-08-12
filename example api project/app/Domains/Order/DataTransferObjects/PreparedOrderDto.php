<?php

namespace App\Domains\Order\DataTransferObjects;

use App\Services\ValueObject\BaseValueObject;

class PreparedOrderDto extends BaseValueObject
{
	private int $userId;
	private array $order;

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }

    /**
     * @return array
     */
    public function getOrder() : array
    {
        return $this->order;
    }

	protected function getSchema(): array
	{
		return [
			'user_id',
			'order',
		];
	}

	protected function map(): void
	{
		$this->userId = $this->attributes['user_id'];
		$this->order = $this->attributes['order'];
	}
}