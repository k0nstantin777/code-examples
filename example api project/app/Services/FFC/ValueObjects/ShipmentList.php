<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\FFC\Enums\OrderType;
use App\Services\FFC\Enums\ShipmentStatus;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ShipmentList extends ItemList
{
    /**
     * @var Collection|Shipment[]
     */
    protected Collection $data;

    protected function fillData(): Collection
    {
        $results = collect();

        if (empty($this->attributes['data'])) {
            return $results;
        }

        foreach ($this->attributes['data'] as $shipmentData) {
            $results->push(Shipment::from([
                'id' => $shipmentData['id'],
                'orderNumber' => $shipmentData['order_number'],
                'createdDate' => Carbon::parse($shipmentData['created_date']),
                'status' => ShipmentStatus::from($shipmentData['status']),
                'orderType' => OrderType::from($shipmentData['order_type']),
                'orderId' => $shipmentData['order_id'],
                'carrier' => $shipmentData['carrier'] ?? null,
                'service' => $shipmentData['service'] ?? null,
                'trackingNumber' => $shipmentData['tracking_number'] ?? null,
                'isShippingProcessing' => $shipmentData['is_shipping_processing'] ?? null,
                'shipDate' => isset($shipmentData['ship_date']) ? Carbon::parse($shipmentData['ship_date']) : null,
                'shippingProcessingAt' => isset($shipmentData['shipping_processing_at']) ?
                    Carbon::parse($shipmentData['shipping_processing_at']) : null,
                'items' => $this->fillShipmentItems($shipmentData['items'] ?? []),
                'addresses' => $this->fillShipmentAddresses($shipmentData['addresses'] ?? []),
            ]));
        }

        return $results;
    }

    protected function fillShipmentItems(array $items): Collection
    {
        $result = collect();
        foreach ($items as $itemData) {
            $result->push(ShipmentItem::from([
                'id' => $itemData['id'],
                'productId' => $itemData['product_id'],
                'sku' => $itemData['sku'],
                'name' => $itemData['name'],
                'price' => $itemData['price'],
                'tax' => $itemData['tax'],
                'quantity' => $itemData['quantity'],
            ]));
        }

        return $result;
    }

    protected function fillShipmentAddresses(array $addresses): Collection
    {
        $result = collect();
        foreach ($addresses as $addressData) {
            $result->push(ShipmentAddress::from([
                'id' => $addressData['id'],
                'type' => $addressData['type'],
                'postal' => $addressData['postal'],
                'state' => $addressData['state'],
                'address1' => $addressData['address1'],
                'city' => $addressData['city'],
                'name' => $addressData['name'],
                'company' => $addressData['company'],
                'address2' => $addressData['address2'] ?? '',
                'country' => $addressData['country'] ?? '',
                'cemeteryId' => $addressData['cemetery_id'] ?? null,
            ]));
        }

        return $result;
    }
}
