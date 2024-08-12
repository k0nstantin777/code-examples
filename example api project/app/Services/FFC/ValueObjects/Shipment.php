<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\FFC\Enums\OrderType;
use App\Services\FFC\Enums\ShipmentStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class Shipment extends Data
{
    public function __construct(
        public int $id,
        public string $orderNumber,
        public ShipmentStatus $status,
        public OrderType $orderType,
        public int $orderId,
        /** @var Collection|ShipmentItem[] $items */
        public Collection $items,
        /** @var Collection|ShipmentAddress[] $addresses */
        public Collection $addresses,
        public Carbon $createdDate,
        public ?bool $isShippingProcessing = null,
        public ?Carbon $shippingProcessingAt = null,
        public ?Carbon $shipDate = null,
        public ?string $carrier = null,
        public ?string $service = null,
        public ?string $trackingNumber = null,
    ) {
    }
}
