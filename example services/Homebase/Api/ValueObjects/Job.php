<?php

namespace App\Services\Homebase\Api\ValueObjects;

use Spatie\LaravelData\Data;

class Job extends Data
{
    public function __construct(
        public int $id,
        public string $level,
        public string $locationUuid,
        public string $pin,
        public ?string $defaultRole = null,
        public ?string $payrollId = null,
        public ?string $wageType = null,
        public ?string $wageRate = null,
        public array $roles = [],
    ) {
    }
}