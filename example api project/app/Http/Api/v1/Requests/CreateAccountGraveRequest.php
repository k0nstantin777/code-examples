<?php

namespace App\Http\Api\v1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccountGraveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cemetery_id' => ['required', 'integer'],
            'section' => ['nullable', 'string', 'min:1', 'max:255'],
            'lot' => ['nullable', 'string', 'min:1', 'max:255'],
            'space' => ['nullable', 'string', 'min:1', 'max:255'],
            'building' => ['nullable', 'string', 'min:1', 'max:255'],
            'tier' => ['nullable','min:1', 'max:255'],
            'notes' => ['nullable','min:1', 'max:1000'],
            'loved_info' => ['required','min:2','max:255'],
            'contact_phone' => ['required','string', 'min:2','max:255'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'cemetery_id' => [
                'description' => 'Cemetery Id from the list of cemeteries (see <a href="/docs/#cemeteries">cemeteries</a> endpoint)',
                'example' => 32,
            ],
            'loved_info' => [
                'description' => 'Grave Loved Info',
                'example' => 'Nik Marshal',
            ],
            'section' => [
                'description' => 'Grave section',
                'example' => 'A',
            ],
            'lot' => [
                'description' => 'Grave lot',
                'example' => '2',
            ],
            'space' => [
                'description' => 'Grave space',
                'example' => '2',
            ],
            'building' => [
                'description' => 'Grave space',
                'example' => '2',
            ],
            'tier' => [
                'description' => 'Grave tier',
                'example' => '2',
            ],
            'notes' => [
                'description' => 'Additional info',
                'example' => 'Some text',
            ],
            'contact_phone' => [
                'description' => 'Contact phone',
                'example' => '+01 321 1231234',
            ],
        ];
    }
}
