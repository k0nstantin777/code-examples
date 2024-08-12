<?php

namespace App\Services\Exchanger\ValueObjects;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ListItems extends DataTransferObject
{
    #[MapFrom('items')]
    public Collection $items;

    #[MapFrom('meta')]
    public MetaData $meta;
}