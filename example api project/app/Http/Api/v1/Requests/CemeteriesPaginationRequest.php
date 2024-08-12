<?php

namespace App\Http\Api\v1\Requests;

class CemeteriesPaginationRequest extends IndexPaginationRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'city' => [
                'bail',
                'nullable',
                'string',
                'min:2',
                'max: 200',
            ],
            'state' => ['bail', 'nullable', 'string', 'size:2'],
        ]);
    }

    protected function getSortRules(): array
    {
        return ['string', 'in:id,name'];
    }

    public function getCity(): string
    {
        return $this->get('city') ?? '';
    }

    public function getState(): string
    {
        return $this->get('state') ?? '';
    }

    public function queryParameters(): array
    {
        return array_merge(parent::queryParameters(), [
            'city' => [
                'description' => 'Field to filter by city. Defaults is empty.',
                'example' => 'Foley',
            ],
            'state' => [
                'description' => 'Field to filter by state. Defaults is empty.',
                'example' => 'AL',
            ],
        ]);
    }
}
