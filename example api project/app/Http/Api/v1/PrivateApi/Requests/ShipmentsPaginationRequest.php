<?php

namespace App\Http\Api\v1\PrivateApi\Requests;

use App\Http\Api\v1\Requests\ShipmentsPaginationRequest as BaseShipmentsPaginationRequest;
use App\Http\Api\v1\Rules\InvalidShipmentIncludesValue;

class ShipmentsPaginationRequest extends BaseShipmentsPaginationRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'user_id' => [
                'nullable',
                'integer',
            ],
            'includes' => [
                'bail',
                'string',
                'min:2',
                'max: 200',
                new InvalidShipmentIncludesValue(),
            ],
            'is_shipping_processing' => ['nullable', 'boolean'],
            'shipping_processing_from' => ['nullable', 'date'],
            'shipping_processing_to' => ['nullable', 'date'],
        ]);
    }

    public function getUserId(): ?string
    {
        return $this->get('user_id') ?? null;
    }

    public function getIncludes(): ?string
    {
        return $this->get('includes') ?? null;
    }

    public function getIsShippingProcessing(): ?string
    {
        return $this->get('is_shipping_processing') ?? null;
    }

    public function getShippingProcessingFrom(): ?string
    {
        return $this->get('shipping_processing_from') ?? '';
    }

    public function getShippingProcessingTo(): ?string
    {
        return $this->get('shipping_processing_to') ?? '';
    }
}
