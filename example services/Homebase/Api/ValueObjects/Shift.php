<?php

namespace App\Services\Homebase\Api\ValueObjects;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

class Shift extends Data
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $jobId,
        public ShiftLabor $labor,
        public CarbonInterface $createdAt,
        public CarbonInterface $updatedAt,
        public CarbonInterface $startAt,
        public CarbonInterface $endAt,
        public ?int $timecardId = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $wageRate = null,
        public ?string $role = null,
        public ?string $department = null,
        public bool $open = false,
        public bool $published = false,
        public bool $scheduled = false,
    ) {
    }
}
