<?php

namespace App\Http\Api\v1\Requests;

use App\Domains\Order\Enums\MemorialType;
use App\Domains\Order\Enums\ShippingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFlowerProgramRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'placements' => ['required', 'array'],
            'placements.*.product_id' => ['required', 'integer'],
            'placements.*.date' => ['required', 'date', 'after:' . now()->toDateString()],
            'placements.*.extras' => ['nullable', 'array'],
            'placements.*.extras.*' => ['required', 'integer'],
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
            'monument' => ['required', 'array'],
            'monument.monument_type' => ['required', 'integer', 'in:0,1,2'],
            'monument.memorial_type' => ['required',  Rule::in(MemorialType::values())],
            'monument.vase_type' => ['nullable', 'integer', 'in:1,2,3,4'],
            'monument.vase_has_plastic' => ['nullable', 'boolean'],
            'monument.vase_size' => ['nullable', 'integer', 'in:3,5,7'],
            'monument.vase_diameter' => ['nullable', 'numeric'],
            'has_expired_notify' => ['nullable', 'boolean'],
            'shipping_service' => ['required', 'in:fedex'],
            'payment_service' => ['required', 'in:invoice']
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'delivery_address_id' => [
                'description' => 'Address Id from the list of addresses (see <a href="/docs/#account">account</a> endpoint)',
                'example' => 1,
            ],
            'payment_address_id' =>[
                'description' => 'Address Id from the list of addresses (see <a href="/docs/#account">account</a> endpoint)',
                'example' => 1,
            ],
            'comment' =>[
                'description' => 'Any additional information on order',
                'example' => 'Please deliver order by 5pm',
            ],
            'coupon' =>[
                'description' => 'If you have the coupon code, fill this field',
                'example' => 'SELL10',
            ],
            'grave_id' =>[
                'description' => 'Grave Id from the list of graves (see <a href="/docs/#account">account</a> endpoint)',
                'example' => 1,
            ],
            'shipping_type' =>[
                'description' => 'Deliver to home address or cemetery.',
                'example' => 'address',
            ],
            'monument.monument_type' => [
                'description' => 'Available monument types: 0 - Vase is sitting on the ground, 1 - Vase is attached to a wall, 2 - Monument or No vase',
                'example' => 0
            ],
            'monument.memorial_type' => [
                'description' => 'Depends on monument type: if 0 - available: Large,Medium; 1 - Mausoleum, Niche, Bud; 2 - Brick, Potted Silk, Saddle, Large Saddle, or Large Potted Silk',
                'example' => 'Large'
            ],
            'monument.vase_type' => [
                'description' => 'Available vase types: 1 - Plastic vase, 2 - Metal vase, 3 - Granit vase, 4 - Vase style already known by Flowers For Cemeteries',
                'example' => 2
            ],
            'monument.vase_has_plastic' => [
                'description' => 'Have the vase plastic inserts? True or false'
            ],
            'monument.vase_size' => [
                'description' => 'Vase size',
                'example' => 3
            ],
            'monument.vase_diameter' => [
                'description' => 'Vase diameter in inches',
                'example' => '10'
            ],
            'shipping_service' => [
                'description' => 'Shipping carrier code, currently only FedEx allowed',
            ],
            'payment_service' => [
                'description' => 'Payment method, currently only Invoice allowed',
            ],
            'placements.*.product_id' => [
                'description' => 'Product ID (see <a href="/docs/#products">products</a> endpoint)',
            ],
            'placements.*.date' => [
                'description' => 'Placement Date',
            ],
            'placements.*.extras.*' => [
                'description' => 'Extras product ids, optional',
            ],
            'has_expired_notify' => [
                'description' => 'If set to true, we will send a notification after the last placement has been submitted.',
            ]
        ];
    }
}
