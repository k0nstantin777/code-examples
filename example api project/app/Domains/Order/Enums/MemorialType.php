<?php

namespace App\Domains\Order\Enums;

use ArchTech\Enums\Values;

enum MemorialType: string
{
    use Values;
    
    case LARGE = 'Large';
    case MEDIUM = 'Medium';
    case MAUSOLEUM = 'Mausoleum';
    case NICHE = 'Niche';
    case BUD = 'Bud';
    case BRICK = 'Brick';
    case POTTED_SILK = 'Potted Silk';
    case SADDLE = 'Saddle';
    case LARGE_SADDLE = 'Large Saddle';
    case LARGE_POTTED_SILK = 'Large Potted Silk';

    public static function getRandomValue() : string
    {
        $values = self::values();

        return $values[array_rand($values)];
    }
}