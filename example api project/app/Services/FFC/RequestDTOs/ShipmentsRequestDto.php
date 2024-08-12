<?php

namespace App\Services\FFC\RequestDTOs;

use App\Services\FFC\Enums\OrderType;
use App\Services\FFC\Enums\ShipmentStatus;

class ShipmentsRequestDto extends ListRequestDto
{
    public function __construct(
        public ?int $userId,
        public string $search = '',
        public ?string $from = null,
        public ?string $to = null,
        public ?string $shipFrom = null,
        public ?string $shipTo = null,
        public ?string $shippingProcessingFrom = null,
        public ?string $shippingProcessingTo = null,
        public ?ShipmentStatus $status = null,
        public ?OrderType $type = null,
        public string $includes = '',
        public ?bool $isShippingProcessing = null,
        int $limit = 100,
        int $offset = 0,
        string $sort = 'id',
        string $sortDirection = 'desc',
    ) {
        parent::__construct($limit, $offset, $sort, $sortDirection);
    }
}
