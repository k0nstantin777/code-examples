<?php

namespace App\Http\Api\v1\Requests;

use App\Http\Requests\BaseFormRequest;

class IndexPaginationRequest extends BaseFormRequest
{
	protected const DEFAULT_LIMIT = 100;
	protected const DEFAULT_OFFSET = 0;
	protected const DEFAULT_SORT = 'id';
	protected const DEFAULT_SORT_DIRECTION = 'desc';

    public function rules(): array
    {
        return [
            'limit' => ['numeric', 'integer', 'min:1', 'max:100'],
            'offset' => ['numeric', 'integer', 'min:0'],
            'sort' => $this->getSortRules(),
            'sort_direction' => ['string', 'in:asc,desc']
        ];
    }

    protected function getSortRules(): array
    {
        return ['string', 'min:2', 'max:20', 'in:id'];
    }

    public function getLimit() : int
    {
        return $this->get('limit') ?? static::DEFAULT_LIMIT;
    }

    public function getOffset() : int
    {
        return $this->get('offset') ?? static::DEFAULT_OFFSET;
    }

    public function getSort() : string
    {
        return $this->get('sort') ?? static::DEFAULT_SORT;
    }

    public function getSortDirection() : string
    {
        return $this->get('sort_direction') ?? static::DEFAULT_SORT_DIRECTION;
    }

	public function queryParameters(): array
	{
		return [
			'sort' => [
				'description' => 'Field to sort by. Defaults to id.',
				'example' => 'code',
			],
			'sort_direction' => [
				'description' => 'Field to sort direction. Defaults to desc.',
				'example' => 'desc',
			],
			'limit' =>[
				'description' => 'Count of rows. Defaults to 100.',
				'example' => 100,
			],
			'offset' =>[
				'description' => 'Offset of start list. Defaults to 0.',
				'example' => 0,
			],
		];
	}
}
