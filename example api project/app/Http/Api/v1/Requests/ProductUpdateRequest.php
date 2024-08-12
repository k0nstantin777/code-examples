<?php

namespace App\Http\Api\v1\Requests;

use App\Http\Requests\BaseFormRequest;

class ProductUpdateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code' => [
				'required',
				'string',
				'min:2',
				'max: 200',
			],
			'stock_level' => ['integer'],
        ];
    }

	public function getCode() : string
	{
		return $this->get('code');
	}

	public function getStockLevel() : ?int
	{
		return $this->get('stock_level');
	}

	public function bodyParameters(): array
	{
		return [
			'sku' => [
				'description' => 'Code of product',
				'example' => 'MA9999',
			],
			'stock_level' => [
				'description' => 'New stock level',
				'example' => 10,
			],
		];
	}
}
