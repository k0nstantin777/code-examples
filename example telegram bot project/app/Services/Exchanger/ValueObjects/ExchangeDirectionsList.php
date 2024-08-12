<?php

namespace App\Services\Exchanger\ValueObjects;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Attributes\MapFrom;

class ExchangeDirectionsList extends ListItems
{
    /**
     * @var ExchangeDirectionListItem[]
     */
    #[MapFrom('items')]
    public Collection $items;
}