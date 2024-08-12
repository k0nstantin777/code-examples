<?php

namespace App\Http\Api\v1\PrivateApi\Resources;

use App\Services\FFC\ValueObjects\OrderCustomer;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderCustomerResource extends JsonResource
{
    /**
     * @var OrderCustomer
     */
    public $resource;

    public function toArray($request) : array
    {
        return [
            'id' => $this->resource->getId(),
            'name' => $this->resource->getName(),
            'email' => $this->resource->getEmail(),
        ];
    }
}
