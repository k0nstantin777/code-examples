<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\Shipment;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    /**
     * @var Shipment
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'status' => $this->resource->status->value,
            'order_number' => $this->resource->orderNumber,
            'order_type' => $this->resource->orderType->value,
            'order_id' => $this->resource->orderId,
            'created_date' => $this->resource->createdDate->format(config('app.date_time_format')),
            'carrier' => $this->when($this->resource->carrier !== null, function () {
                return $this->resource->carrier;
            }),
            'service' => $this->when($this->resource->service !== null, function () {
                return $this->resource->service;
            }),
            'tracking_number' => $this->when($this->resource->trackingNumber !== null, function () {
                return $this->resource->trackingNumber;
            }),
            'ship_date' => $this->when($this->resource->shipDate !== null, function () {
                return $this->resource->shipDate->format(config('app.date_time_format'));
            }),
        ];
    }
}
