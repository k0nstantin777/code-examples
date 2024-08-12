<?php

namespace App\Services\Homebase\Api\ValueObjects;

use Spatie\LaravelData\Data;

class ShiftLabor extends Data
{
    public function __construct(
        public float $scheduledHours,
        public float $scheduledOvertime,
        public float $scheduledRegular,
        public float $scheduledDailyOvertime,
        public float $scheduledWeeklyOvertime,
        public float $scheduledDoubleOvertimes,
        public float $scheduledSeventhDayOvertime15,
        public float $scheduledSeventhDayOvertime20,
        public float $scheduledUnpaidBreaksHours,
        public float $scheduledCosts,
        public float $scheduledOvertimeCosts,
        public float $scheduledSpreadOfHours,
        public float $scheduledBlueLawsHours,
        public ?string $wageType = null,
    ) {
    }
}