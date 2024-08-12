<?php

namespace App\Http\Api\v1\Rules;

class InvalidFlowerProgramIncludesValue extends BaseInvalidIncludesValueRule
{
    protected const ALLOWED_VALUES = [
		'delivery_address',
		'payment_address',
		'delivery_service',
		'payment_service',
		'grave',
		'placements',
		'coupon',
        'monument'
	];
}
