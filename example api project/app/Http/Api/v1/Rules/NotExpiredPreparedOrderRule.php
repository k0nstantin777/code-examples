<?php

namespace App\Http\Api\v1\Rules;

use App\Domains\Order\Services\PreparedOrderService;
use Illuminate\Contracts\Validation\Rule;

class NotExpiredPreparedOrderRule implements Rule
{
    private const EXPIRED_MIN = 5;

    public function __construct(
        private PreparedOrderService $preparedOrderService,
    ) {
    }

    public function passes($attribute, $value): bool
	{
        $preparedOrder = $this->preparedOrderService->getById($value);

		return $preparedOrder->created_at->greaterThan(now()->subMinutes(self::EXPIRED_MIN));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
	{
        return 'The prepared order is expired.';
    }
}
