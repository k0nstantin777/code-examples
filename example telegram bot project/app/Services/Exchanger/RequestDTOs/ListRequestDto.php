<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ListRequestDto extends DataTransferObject
{
    #[MapFrom('limit')]
    public int $limit = 100;

    #[MapFrom('offset')]
    public int $offset = 0;

    #[MapFrom('sort')]
    public string $sort = 'id';

    #[MapFrom('sort_direction')]
    public string $sortDirection = 'asc';
}