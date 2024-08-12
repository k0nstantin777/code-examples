<?php

namespace App\Http\Api\v1\Rules;

class InvalidAccountInfoIncludesValue extends BaseInvalidIncludesValueRule
{
    protected const ALLOWED_VALUES = [
		'addresses',
		'graves',
	];
}
