<?php

namespace App\Domains\Order\Enums;

use ArchTech\Enums\Values;

enum ShippingType: string
{
    use Values;

    case ADDRESS = 'address';
    case CEMETERY = 'cemetery';

    public static function getRandomValue() : string
    {
        $values = self::values();

        return $values[array_rand($values)];
    }
}