<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateFlowerProgramRequestDto;
use Illuminate\Validation\ValidationException;

class CreateFlowerProgram extends BaseJsonRpcEndpoint
{
	/**
	 * @param mixed ...$arguments
	 * @return array
     * @throws JsonRpcErrorResponseException|ValidationException
     */
	public function execute(...$arguments) : array
	{
		/* @var CreateFlowerProgramRequestDto $dto */
		[$dto] = $arguments;

        return $this->jsonRpcClient->send('flower-programs.store', [
            'user_id' => $dto->getUserId(),
            'shipping_type' => $dto->getShippingType(),
            'delivery_address_id' => $dto->getDeliveryAddressId(),
            'payment_address_id' => $dto->getPaymentAddressId(),
            'placements' => $dto->getPlacements(),
            'comment' => $dto->getComment(),
            'coupon' => $dto->getCoupon(),
            'grave_id' => $dto->getGraveId(),
            'delivery_method' => $dto->getShippingService(),
            'payment_method' => $dto->getPaymentService(),
            'monument' => $dto->getMonument(),
            'has_expired_notify' => $dto->isHasExpiredNotify(),
		]);
	}
}