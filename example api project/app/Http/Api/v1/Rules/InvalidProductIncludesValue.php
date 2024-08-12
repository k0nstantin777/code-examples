<?php

namespace App\Http\Api\v1\Rules;

class InvalidProductIncludesValue extends BaseInvalidIncludesValueRule
{
    protected const ALLOWED_VALUES = [
		'category',
		'price',
		'image',
        'stock'
	];
}
