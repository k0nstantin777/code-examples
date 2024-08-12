<?php

namespace App\Http\Api\v1\Rules;

class InvalidShipmentIncludesValue extends BaseInvalidIncludesValueRule
{
    protected const ALLOWED_VALUES = [
        'items',
        'addresses',
    ];
}
