<?php

namespace App\Services\Homebase\Api\ValueObjects;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

class Timecard extends Data
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $jobId,
        public int $shiftId,
        public TimecardLabor $labor,
        public CarbonInterface $createdAt,
        public CarbonInterface $updatedAt,
        public ?CarbonInterface $clockIn = null,
        public ?CarbonInterface $clockOut = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $payrollId = null,
        public ?string $role = null,
        public ?string $department = null,
        /** @var Timebreak[] */
        public array $timebreaks = [],
        public bool $approved = false,
    ) {
    }
}