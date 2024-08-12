<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Spatie\LaravelData\Data;

class AccountGrave extends Data
{
    public function __construct(
        public int $id,
        public Cemetery $cemetery,
        public string $stateName = '',
        public string $city = '',
        public string $section = '',
        public string $lot = '',
        public string $tier = '',
        public string $space = '',
        public string $building = '',
        public string $notes = '',
        public string $lovedInfo = '',
        public Category|null $memorialType = null,
        public Category|null $memorialTypeSub = null,
        public string $graveImage = '',
        public string $contactPhone = '',
    ) {
    }

    /**
     * @throws InvalidSchemaException
     */
    public static function makeFromData(array $data): self
    {
        return self::from([
            'id' => $data['id'],
            'cemetery' => Cemetery::makeFromData($data['cemetery']),
            'stateName' => $data['state_name'] ?? '',
            'city' => $data['city'] ?? '',
            'section' => $data['section'] ?? '',
            'lot' => $data['lot'] ?? '',
            'space' => $data['space'] ?? '',
            'building' => $data['building'] ?? '',
            'tier' => $data['tier'] ?? '',
            'notes' => $data['notes'] ?? '',
            'lovedInfo' => $data['loved_info'] ?? '',
            'memorialType' => isset($data['memorial_type']) ? new Category($data['memorial_type']) : null,
            'memorialTypeSub' => isset($data['memorial_type_sub']) ? new Category($data['memorial_type_sub']) : null,
            'contactPhone' => $data['contact_phone'] ?? '',
            'graveImage' => $data['grave_image'] ?? '',
        ]);
    }
}