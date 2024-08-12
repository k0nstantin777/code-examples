<?php

namespace App\Http\Api\v1\Requests;

use App\Services\FFC\Enums\OrderType;
use App\Services\FFC\Enums\ShipmentStatus;
use Illuminate\Validation\Rule;

class ShipmentsPaginationRequest extends IndexPaginationRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'type' => [
                'bail',
                'nullable',
                Rule::in(OrderType::values())
            ],
            'search' => ['nullable', 'string', 'between:3,50'],
            'status' => ['nullable',  Rule::in(ShipmentStatus::values())],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'ship_from' => ['nullable', 'date'],
            'ship_to' => ['nullable', 'date'],
        ]);
    }

    protected function getSortRules(): array
    {
        return ['string', 'min:2', 'max:22', 'in:id,created_date,ship_date,shipping_processing_at'];
    }

    public function getType(): ?string
    {
        return $this->get('type') ?? null;
    }

    public function getStatus(): ?string
    {
        return $this->get('status') ?? null;
    }

    public function getSearch(): string
    {
        return $this->get('search') ?? '';
    }

    public function getFrom(): ?string
    {
        return $this->get('from') ?? '';
    }

    public function getTo(): ?string
    {
        return $this->get('to') ?? '';
    }

    public function getShipFrom(): ?string
    {
        return $this->get('ship_from') ?? '';
    }

    public function getShipTo(): ?string
    {
        return $this->get('ship_to') ?? '';
    }

    public function queryParameters(): array
    {
        return array_merge(parent::queryParameters(), [
            'type' => [
                'description' => 'Field to filter by order type. Defaults is empty',
                'example' => 'order',
            ],
            'status' => [
                'description' => 'Field to filter by status. Defaults is empty',
                'example' => 'shipped',
            ],
            'search' => [
                'description' => 'Field to filter by shipment ID, order number or part of it. Defaults is empty',
                'example' => 'RS-',
            ],
            'from' => [
                'description' => 'Field to filter by created date from any date. Defaults is empty',
                'example' => '2022-01-01',
            ],
            'to' => [
                'description' => 'Field to filter by created date to any date. Defaults is empty',
                'example' => '2022-12-01',
            ],
            'ship_from' => [
                'description' => 'Field to filter by shipped date from any date. Defaults is empty',
                'example' => '2022-01-01',
            ],
            'ship_to' => [
                'description' => 'Field to filter by shipped date to any date. Defaults is empty',
                'example' => '2022-12-01',
            ]
        ]);
    }
}
