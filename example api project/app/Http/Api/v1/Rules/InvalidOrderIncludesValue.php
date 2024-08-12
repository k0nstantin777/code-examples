<?php

namespace App\Http\Api\v1\Rules;

class InvalidOrderIncludesValue extends BaseInvalidIncludesValueRule
{
    protected const ALLOWED_VALUES = [
        'delivery_address',
        'payment_address',
        'delivery_service',
        'payment_service',
        'grave',
        'products',
        'coupon'
    ];
}
