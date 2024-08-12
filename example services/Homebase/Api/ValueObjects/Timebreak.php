<?php

namespace App\Services\Homebase\Api\ValueObjects;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

class Timebreak extends Data
{
    public function __construct(
        public int $id,
        public int $mandatedBreakId,
        public int $timecardId,
        public int $duration,
        public int $workPeriod,
        public CarbonInterface $createdAt,
        public CarbonInterface $updatedAt,
        public CarbonInterface $startAt,
        public CarbonInterface $endAt,
        public bool $paid = false,
    ) {
    }
}
