<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\AccountGrave;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountGraveResource extends JsonResource
{
    /**
     * @var AccountGrave
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'cemetery' => $this->when((bool) $this->resource->cemetery, function () {
                return new CemeteryResource($this->resource->cemetery);
            }),
            'state_name' => $this->resource->stateName,
            'city' => $this->resource->city,
            'section' => $this->resource->section,
            'lot' => $this->resource->lot,
            'space' => $this->resource->space,
            'building' => $this->resource->building,
            'tier' => $this->resource->tier,
            'notes' => $this->resource->notes,
            'loved_info' => $this->resource->lovedInfo,
            'memorial_type' => $this->when((bool) $this->resource->memorialType, function () {
                return new CategoryResource($this->resource->memorialType);
            }),
            'memorial_type_sub' => $this->when((bool) $this->resource->memorialType, function () {
                return new CategoryResource($this->resource->memorialTypeSub);
            }),
            'contact_phone' => $this->resource->contactPhone,
            'grave_image' => $this->resource->graveImage,
        ];
    }
}
