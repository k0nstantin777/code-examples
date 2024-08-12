<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class MetaData extends DataTransferObject
{
    #[MapFrom('limit')]
    public int $limit;

    #[MapFrom('offset')]
    public int $offset;

    #[MapFrom('total')]
    public int $total;
}