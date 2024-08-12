<?php

namespace App\Services\Homebase\Api\DataTransferObjects;

class ListLocationRequestDTO extends ListRequestDTO
{
    public function __construct(
        public string $locationUuid,
        int $page = 1,
        int $perPage = 100,
    ) {
        parent::__construct($page, $perPage);
    }
}