<?php

namespace App\Http\Api\v1\Requests;

use App\Http\Api\v1\Rules\CorrectShipRateRule;
use App\Http\Api\v1\Rules\NotExpiredPreparedOrderRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'prepared_order_id' => [
                'required',
                Rule::exists('prepared_orders', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
                app(NotExpiredPreparedOrderRule::class),
            ],
            'ship_rate' => [
                'bail',
                'required',
                'array',
                app(CorrectShipRateRule::class, ['prepareOrderId' => $this->request->get('prepared_order_id') ?? 0])
            ],
            'ship_rate.id' => ['required', 'integer'],
            'ship_rate.cost' => ['required', 'numeric'],
            'ship_rate.serviceName' => ['required', 'string'],
            'ship_rate.carrierName' => ['required', 'string'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'prepared_order_id' => [
                'description' => 'Id from the <a href="/docs/#orders-POSTapi-v1-orders-prepare">Order Prepare Response</a>',
                'example' => 1,
            ],
            'ship_rate.id' => [
                'example' => 1,
            ],
            'ship_rate.cost' => [
                'example' => 10,
            ],
            'ship_rate.serviceName' => [
                'example' => 'UPS Ground',
            ],
            'ship_rate.carrierName' => [
                'example' => 'UPS',
            ]
        ];
    }
}
