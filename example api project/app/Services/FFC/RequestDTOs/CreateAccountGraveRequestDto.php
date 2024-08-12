<?php

namespace App\Services\FFC\RequestDTOs;

use Spatie\LaravelData\Data;

class CreateAccountGraveRequestDto extends Data
{
    public function __construct(
        public int $userId,
        public int $cemeteryId,
        public string $lovedInfo,
        public string $contactPhone,
        public string $section = '',
        public string $lot = '',
        public string $space = '',
        public string $building = '',
        public string $tier = '',
        public string $notes = '',
    ) {
    }
}
