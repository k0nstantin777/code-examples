<?php

namespace App\Http\Api\v1\Requests;

use App\Domains\Order\Enums\ShippingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrepareOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'integer'],
            'products.*.quantity' => ['required', 'integer'],
            'delivery_address_id' => [
                'required_if:shipping_type,' . ShippingType::ADDRESS->value,
                'integer'
            ],
            'payment_address_id' => ['required', 'integer'],
            'comment' => ['nullable', 'string', 'min:2', 'max:1000'],
            'coupon' => ['nullable', 'string', 'min:2', 'max:20'],
            'grave_id' => [
                'required_if:shipping_type,' . ShippingType::CEMETERY->value,
                'integer'
            ],
            'shipping_type' => ['required', Rule::in(ShippingType::values())],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'products.*.id' => [
                'description' => 'Product Id',
                'example' => 22,
            ],
            'products.*.quantity' => [
                'description' => 'Product Quantity',
                'example' => 11,
            ],
            'delivery_address_id' => [
                'description' => 'Address Id from the list of addresses (see <a href="/docs/#account">account</a> endpoint)',
                'example' => 1,
            ],
            'payment_address_id' => [
                'description' => 'Address Id from the list of addresses (see <a href="/docs/#account">account</a> endpoint)',
                'example' => 1,
            ],
            'comment' => [
                'description' => 'Any additional information on order',
                'example' => 'Please deliver order by 5pm',
            ],
            'coupon' => [
                'description' => 'If you have the coupon code, fill this field',
                'example' => 'SELL10',
            ],
            'grave_id' => [
                'description' => 'Grave Id from the list of graves (see <a href="/docs/#account">account</a> endpoint)',
                'example' => 1,
            ],
            'shipping_type' => [
                'description' => 'Deliver to home address or cemetery.',
                'example' => 'address',
            ],
        ];
    }
}
