<?php

namespace App\Http\Api\v1\Requests;

use App\Http\Api\v1\Rules\InvalidProductIncludesValue;

class ProductsPaginationRequest extends IndexPaginationRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'includes' => [
                'bail',
                'string',
                'min:2',
                'max: 200',
                new InvalidProductIncludesValue(),
            ],
            'in_stock' => ['boolean', 'in:0,1'],
            'search' => ['nullable', 'string', 'between:3,20'],
            'category_id' => ['nullable', 'integer']
        ]);
    }

    protected function getSortRules(): array
    {
        return ['string', 'in:id,code,label'];
    }

    public function getIncludes(): string
    {
        return $this->get('includes') ?? '';
    }

    public function getInStock(): bool
    {
        return $this->get('in_stock') ?? false;
    }

    public function getSearch(): string
    {
        return $this->get('search') ?? '';
    }

    public function getCategoryId(): ?int
    {
        return $this->get('category_id');
    }

    public function queryParameters(): array
    {
        return array_merge(parent::queryParameters(), [
            'includes' => [
                'description' => 'Field to attach additional data to response. Must be string of words: category, price, stock or image, comma separated. Defaults is empty.',
                'example' => 'category,image,price',
            ],
            'in_stock' => [
                'description' => 'Field to filter by only available products. Defaults to 0.',
                'example' => 'desc',
            ],
            'search' => [
                'description' => 'Field to filter by product code, label or part of its. Defaults is empty',
                'example' => 'MD1',
            ],
            'category_id' => [
                'description' => 'Field to filter by category id. Defaults is empty.',
                'example' => 7,
            ],
        ]);
    }
}
