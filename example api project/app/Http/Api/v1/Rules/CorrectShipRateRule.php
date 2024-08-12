<?php

namespace App\Http\Api\v1\Rules;

use App\Domains\Order\Services\PreparedOrderService;
use Illuminate\Contracts\Validation\Rule;

class CorrectShipRateRule implements Rule
{
    public function __construct(
        private readonly int $prepareOrderId,
        private readonly PreparedOrderService $preparedOrderService,
    ) {
    }

    public function passes($attribute, $value): bool
	{
        if (!$value || !isset($value['id'], $value['cost'], $value['serviceName'], $value['carrierName'])) {
			return false;
		}

        $preparedOrder = $this->preparedOrderService->getById($this->prepareOrderId);

        if(!isset($preparedOrder->order['shipRates'])){
            return true;
        }

        $rates = $preparedOrder->order['shipRates'];

        foreach($rates as $rate) {
            if ($rate == $value) {
                return true;
            }
        }

		return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
	{
        return 'The ship rate incorrect.';
    }
}
