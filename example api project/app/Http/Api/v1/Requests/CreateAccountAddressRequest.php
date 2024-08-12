<?php

namespace App\Http\Api\v1\Requests;

use App\Services\FFC\Enums\UserSalutation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAccountAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'postal' => ['required', 'string', 'min:4', 'max:20'],
            'state' => ['required', 'string', 'size:2'],
            'address1' => ['required', 'string', 'min:5', 'max:255'],
            'address2' => ['nullable', 'string', 'min:5', 'max:255'],
            'city' => ['required', 'string', 'min:5', 'max:255'],
            'telephone' => ['nullable', 'string', 'min:5', 'max:255'],
            'firstname' => ['required_without:company', 'min:2', 'max:255'],
            'lastname' => ['required_without:company', 'min:2', 'max:255'],
            'company' => ['required_without:firstname,lastname', 'min:2', 'max:255'],
            'email' => ['required', 'email'],
            'salutation' => ['required', Rule::in(UserSalutation::values())],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'postal' => [
                'description' => 'Zip code',
                'example' => '04432',
            ],
            'state' => [
                'description' => 'USA state abbreviation',
                'example' => 'GA',
            ],
            'city' => [
                'description' => 'Address City',
                'example' => 'New York',
            ],
            'telephone' => [
                'description' => 'Contact phone number',
                'example' => '+01 321 1231234',
            ],
            'firstname' => [
                'description' => 'First Name',
                'example' => 'Nik',
            ],
            'lastname' => [
                'description' => 'Last Name',
                'example' => 'Marshal',
            ],
            'company' => [
                'description' => 'Company Title',
                'example' => 'New Cemetery',
            ],
            'email' => [
                'description' => 'Contact email',
                'example' => 'new-cemetery@email.com',
            ],
            'salutation' => [
                'description' => 'Account salutation',
                'example' => 'company',
            ],
            'address1' => [
                'description' => 'Account address',
                'example' => 'Street, 21 - 2',
            ],
            'address2' => [
                'description' => 'Additional address information',
                'example' => '2nd floor',
            ],
        ];
    }
}
