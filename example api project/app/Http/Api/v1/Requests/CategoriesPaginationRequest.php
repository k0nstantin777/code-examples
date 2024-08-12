<?php

namespace App\Http\Api\v1\Requests;

class CategoriesPaginationRequest extends IndexPaginationRequest
{
	protected function getSortRules(): array
    {
        return ['string', 'in:id,code,label'];
    }
}
