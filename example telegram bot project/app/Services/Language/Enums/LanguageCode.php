<?php

namespace App\Services\Language\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum LanguageCode : string
{
    use Values, InvokableCases;

    case EN = 'en';
    case RU = 'ru';

    public function label() : string
    {
        return match ($this) {
            LanguageCode::EN => 'English',
            LanguageCode::RU => 'Русский',
        };
    }
}
