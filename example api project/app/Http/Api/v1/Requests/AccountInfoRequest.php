<?php

namespace App\Http\Api\v1\Requests;

use App\Http\Api\v1\Rules\InvalidAccountInfoIncludesValue;
use Illuminate\Foundation\Http\FormRequest;

class AccountInfoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'includes' => [
                'bail',
                'string',
                'min:2',
                'max: 200',
                new InvalidAccountInfoIncludesValue(),
            ]
        ];
    }


    public function getIncludes(): string
    {
        return $this->get('includes') ?? '';
    }

    public function queryParameters(): array
    {
        return [
            'includes' => [
                'description' => 'Field to attach additional data to response. Must be string of words: addresses or graves, comma separated. Defaults is empty.',
                'example' => 'addresses,graves',
            ],
        ];
    }
}
