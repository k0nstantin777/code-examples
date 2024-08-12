<?php

namespace App\Services\Language;

use App\Services\Language\Enums\LanguageCode;

class LanguageService
{
    public function getAllowedLanguages() : array
    {
        $result = [];

        foreach (LanguageCode::cases() as $languageCode) {
            $result[$languageCode->value] = $languageCode->label();
        }

        return $result;
    }

    public function setAppLanguage(LanguageCode $languageCode) : void
    {
        app()->setLocale($languageCode->value);
    }

    public function getAppLanguage() : string
    {
        return app()->getLocale();
    }
}
