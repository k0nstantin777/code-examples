<?php

namespace App\Services\FFC\ValueObjects;

use Illuminate\Support\Collection;

class CemeteryList extends ItemList
{
    /**
     * @var Collection|Cemetery[]
     */
    protected Collection $data;

    protected function fillData(): Collection
    {
        $results = collect();

        if (empty($this->attributes['data'])) {
            return $results;
        }

        foreach ($this->attributes['data'] as $cemeteryData) {
            $results->push(Cemetery::makeFromData($cemeteryData));
        }

        return $results;
    }
}
