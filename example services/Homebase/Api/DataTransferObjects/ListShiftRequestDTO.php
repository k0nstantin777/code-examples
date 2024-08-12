<?php

namespace App\Services\Homebase\Api\DataTransferObjects;

use Carbon\CarbonInterface;

class ListShiftRequestDTO extends ListLocationRequestDTO
{
    public function __construct(
        public CarbonInterface $startDate,
        public CarbonInterface $endDate,
        string $locationUuid,
        int $page = 1,
        int $perPage = 100,
        public bool $open = false,
        public bool $withNote = false,
        public string $dateFilter = 'start_at'
    ) {
        parent::__construct($locationUuid, $page, $perPage);
    }
}