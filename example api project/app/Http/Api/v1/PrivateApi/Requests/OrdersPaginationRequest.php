<?php

namespace App\Http\Api\v1\PrivateApi\Requests;

use App\Http\Api\v1\Requests\OrdersPaginationRequest as BaseOrderPaginationRequest;

class OrdersPaginationRequest extends BaseOrderPaginationRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'user_id' => [
                'nullable',
                'integer',
            ],
        ]);
    }

    public function getUserId(): ?int
    {
        return $this->get('user_id');
    }
}
