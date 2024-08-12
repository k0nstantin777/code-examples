<?php

namespace App\Services\Homebase\Api\DataTransferObjects;

use Carbon\CarbonInterface;

class ListTimecardRequestDTO extends ListLocationRequestDTO
{
    public function __construct(
        public CarbonInterface $startDate,
        public CarbonInterface $endDate,
        string $locationUuid,
        int $page = 1,
        int $perPage = 100,
        public string $dateFilter = 'clock_in'
    ) {
        parent::__construct($locationUuid, $page, $perPage);
    }
}