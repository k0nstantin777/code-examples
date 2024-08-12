<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\AccountInfo;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountInfoResource extends JsonResource
{
    /**
     * @var AccountInfo
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getId(),
            'name' => $this->resource->getName(),
            'email' => $this->resource->getEmail(),
            'addresses' => $this->when($this->resource->getAddresses()->isNotEmpty(), function () {
                return AccountAddressResource::collection($this->resource->getAddresses());
            }),
            'graves' => $this->when($this->resource->getGraves()->isNotEmpty(), function () {
                return AccountGraveResource::collection($this->resource->getGraves());
            }),
        ];
    }
}
