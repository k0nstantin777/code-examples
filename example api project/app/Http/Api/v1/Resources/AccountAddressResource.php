<?php

namespace App\Http\Api\v1\Resources;

use App\Services\FFC\ValueObjects\AccountAddress;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountAddressResource extends JsonResource
{
	/**
	 * @var AccountAddress
	 */
	public  $resource;

	public function toArray($request) : array
    {
        return [
            'id' => $this->resource->getId(),
			'postal' => $this->resource->getPostal(),
			'state' => $this->resource->getState(),
			'address1' => $this->resource->getAddress1(),
			'address2' => $this->resource->getAddress2(),
			'city' => $this->resource->getCity(),
			'telephone' => $this->resource->getTelephone(),
			'firstname' => $this->resource->getFirstname(),
			'lastname' => $this->resource->getLastname(),
			'company' => $this->resource->getCompany(),
			'email' => $this->resource->getEmail(),
			'salutation' => $this->resource->getSalutation(),
        ];
    }
}
