<?php

namespace App\Services\FFC\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string ORDER
 * @method static string FLOWER_PROGRAM
 */
enum OrderType : string
{
    use Values;
    use InvokableCases;

    case ORDER = 'order';
    case FLOWER_PROGRAM = 'flower_program';
}
