<?php

namespace App\Services\FFC\RequestDTOs;

class CreateOrderRequestDto extends CalculateOrderRequestDto
{
    protected array $shipRate;

    /**
     * @return array
     */
    public function getShipRate(): array
    {
        return $this->shipRate;
    }

    protected function getSchema(): array
    {
        return array_merge([
            'ship_rate',
        ], parent::getSchema());
    }

    protected function map(): void
    {
        $this->shipRate = $this->attributes['ship_rate'];

        parent::map();
    }
}
