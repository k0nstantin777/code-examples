<?php

namespace App\Http\Api\v1\Rules;

use Illuminate\Contracts\Validation\Rule;

class BaseInvalidIncludesValueRule implements Rule
{
    protected const ALLOWED_VALUES = [];

    protected const DELIMITER = ',';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!$value) {
            return true;
        }

        $checkingString = str_replace(' ', '', $value);
        $checkingWords = explode(static::DELIMITER, $checkingString);

        foreach ($checkingWords as $checkWord) {
            if (false === in_array($checkWord, static::ALLOWED_VALUES, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Includes contains invalid value(s)';
    }
}
