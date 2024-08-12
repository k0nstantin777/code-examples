<?php

namespace App\Http\Api\v1\PrivateApi\Resources;

use App\Http\Api\v1\Resources\ShipmentResource as BaseShipmentResource;
use App\Services\FFC\ValueObjects\Shipment;

class ShipmentResource extends BaseShipmentResource
{
    /**
     * @var Shipment
     */
    public $resource;

    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'items' => $this->when($this->resource->items->isNotEmpty(), function () {
                return ShipmentItemResource::collection($this->resource->items);
            }),
            'addresses' => $this->when($this->resource->addresses->isNotEmpty(), function () {
                return ShipmentAddressResource::collection($this->resource->addresses);
            }),
            'is_shipping_processing' => $this->resource->isShippingProcessing,
            'shipping_processing_at' =>  $this->when($this->resource->shippingProcessingAt !== null, function () {
                return $this->resource->shippingProcessingAt->format(config('app.date_time_format'));
            }),
        ]);
    }
}
