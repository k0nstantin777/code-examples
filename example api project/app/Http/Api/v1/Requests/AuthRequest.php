<?php

namespace App\Http\Api\v1\Requests;

use App\Http\Requests\BaseFormRequest;

class AuthRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
			'password' => ['required', 'string']
        ];
    }
}
