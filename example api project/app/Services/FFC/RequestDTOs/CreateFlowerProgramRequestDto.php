<?php

namespace App\Services\FFC\RequestDTOs;


use App\Services\ValueObject\BaseValueObject;

class CreateFlowerProgramRequestDto extends BaseValueObject
{
	protected ?int $deliveryAddressId;
	protected int $paymentAddressId;
	protected ?int $graveId;
    protected string $comment;
    protected string $coupon;
    protected string $shippingType;
    protected string $shippingService;
    protected string $paymentService;
    protected array $placements;
    protected bool $hasExpiredNotify;
    protected array $monument;
	protected int $userId;

	/**
	 * @return int
	 */
	public function getUserId(): int
	{
		return $this->userId;
	}

    /**
     * @return int|null
     */
    public function getDeliveryAddressId() : ?int
    {
        return $this->deliveryAddressId;
    }

    /**
     * @return int
     */
    public function getPaymentAddressId() : int
    {
        return $this->paymentAddressId;
    }

    /**
     * @return int|null
     */
    public function getGraveId() : ?int
    {
        return $this->graveId;
    }

    /**
     * @return array
     */
    public function getPlacements() : array
    {
        return $this->placements;
    }

    /**
     * @return string
     */
    public function getComment() : string
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getCoupon() : string
    {
        return $this->coupon;
    }

    /**
     * @return string
     */
    public function getShippingType() : string
    {
        return $this->shippingType;
    }

    /**
     * @return string
     */
    public function getShippingService() : string
    {
        return $this->shippingService;
    }

    /**
     * @return string
     */
    public function getPaymentService() : string
    {
        return $this->paymentService;
    }

    /**
     * @return bool
     */
    public function isHasExpiredNotify() : bool
    {
        return $this->hasExpiredNotify;
    }

    /**
     * @return array
     */
    public function getMonument() : array
    {
        return $this->monument;
    }

	protected function getSchema(): array
	{
		return [
			'user_id',
			'?delivery_address_id',
			'payment_address_id',
            'shipping_service',
            'payment_service',
            'shipping_type',
            'placements',
            'monument',
            '?has_expired_notify',
			'?grave_id',
            '?comment',
            '?coupon'
		];
	}

	protected function map(): void
	{
		$this->deliveryAddressId = $this->attributes['delivery_address_id'] ?? null;
		$this->paymentAddressId = $this->attributes['payment_address_id'];
		$this->placements = $this->attributes['placements'];
		$this->userId = $this->attributes['user_id'];
		$this->shippingType = $this->attributes['shipping_type'];
		$this->coupon = $this->attributes['coupon'] ?? '';
		$this->graveId = $this->attributes['grave_id'] ?? null;
		$this->comment = $this->attributes['comment'] ?? '';
		$this->hasExpiredNotify = $this->attributes['has_expired_notify'] ?? false;
		$this->monument = $this->attributes['monument'];
		$this->paymentService = $this->attributes['payment_service'];
		$this->shippingService = $this->attributes['shipping_service'];
	}
}