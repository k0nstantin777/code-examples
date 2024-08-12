<?php

namespace App\Services\Exchanger\ValueObjects;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Attributes\MapFrom;

class TelegramBotsList extends ListItems
{
    /**
     * @var TelegramBotListItem[]
     */
    #[MapFrom('items')]
    public Collection $items;
}