<?php

namespace App\Http\Api\v1\Requests;

use App\Http\Api\v1\Rules\InvalidFlowerProgramIncludesValue;

class FlowerProgramsPaginationRequest extends IndexPaginationRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'includes' => [
				'bail',
				'string',
				'min:2',
				'max: 200',
				new InvalidFlowerProgramIncludesValue(),
			],
			'search' => ['nullable', 'string', 'between:3,50'],
			'status' => ['nullable', 'in:active,shipped,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);
    }

    protected function getSortRules(): array
    {
        return ['string', 'min:2', 'max:20', 'in:id,created_date'];
    }

	public function getIncludes() : string
	{
		return $this->get('includes') ?? '';
	}

	public function getStatus() : string
	{
		return $this->get('status') ?? '';
	}

    public function getSearch() : string
    {
        return $this->get('search') ?? '';
    }

    public function getFrom() : ?string
    {
        return $this->get('from') ?? '';
    }

    public function getTo() : ?string
    {
        return $this->get('to') ?? '';
    }

	public function queryParameters(): array
	{
		return array_merge(parent::queryParameters(), [
			'includes' => [
				'description' => 'Field to attach additional data to response. Must be string of words: 
				 delivery_address, payment_address, delivery_service, payment_service, grave, placements, coupon, monument comma separated. Defaults is empty.',
				'example' => 'delivery_address,delivery_service,payment_service,placements, monument',
			],
			'status' => [
				'description' => 'Field to filter by status. Defaults is empty',
				'example' => 'active',
			],
            'search' => [
                'description' => 'Field to filter by flower program ID, order number or part of it. Defaults is empty',
                'example' => 'RS-',
            ],
            'from' => [
                'description' => 'Field to filter by created date from any date. Defaults is empty',
                'example' => '2022-01-01',
            ],
            'to' => [
                'description' => 'Field to filter by created date to any date. Defaults is empty',
                'example' => '2022-12-01',
            ]
		]);
	}
}
