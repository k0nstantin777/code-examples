<?php

namespace App\Services\Homebase\Api\ValueObjects;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

class Employee extends Data
{
    public function __construct(
        public int $id,
        public Job $job,
        public CarbonInterface $createdAt,
        public CarbonInterface $updatedAt,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $phone = null,
    ) {
    }
}