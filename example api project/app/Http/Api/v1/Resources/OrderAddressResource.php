<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\OrderAddress;

class OrderAddressResource extends AccountAddressResource
{
	/**
	 * @var OrderAddress
	 */
	public $resource;

	public function toArray($request) : array
	{
		return array_merge(parent::toArray($request),[
			'address_id' => $this->resource->getAddressId(),
		]);
	}
}
