<?php

namespace App\Services\Homebase\Api\ValueObjects;

use Spatie\LaravelData\Data;

class TimecardLabor extends Data
{
    public function __construct(
        public float $breakPenalty,
        public float $costs,
        public float $cashTips,
        public float $creditTips,
        public float $weeklyOvertime,
        public float $paidTimeOffHours,
        public float $timeOffHours,
        public float $unpaidBreakHours,
        public float $regularHours,
        public float $paidHours,
        public float $scheduledHours,
        public float $dailyOvertime,
        public float $doubleOvertime,
        public float $seventhDayOvertime15,
        public float $seventhDayOvertime20,
        public ?string $wageType = null,
        public ?string $wageRate = null,
    ) {
    }
}