<?php

namespace App\Services\FFC\RequestDTOs;


use App\Services\ValueObject\BaseValueObject;

class CalculateOrderRequestDto extends BaseValueObject
{
	protected ?int $deliveryAddressId;
	protected int $paymentAddressId;
	protected ?int $graveId;
    protected array $products;
    protected string $comment;
    protected string $coupon;
    protected string $shippingType;
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
    public function getProducts() : array
    {
        return $this->products;
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

	protected function getSchema(): array
	{
		return [
			'user_id',
			'?delivery_address_id',
			'payment_address_id',
            'shipping_type',
            'products',
			'?grave_id',
            '?comment',
            '?coupon'
		];
	}

	protected function map(): void
	{
		$this->deliveryAddressId = $this->attributes['delivery_address_id'] ?? null;
		$this->paymentAddressId = $this->attributes['payment_address_id'];
		$this->products = $this->attributes['products'];
		$this->userId = $this->attributes['user_id'];
		$this->shippingType = $this->attributes['shipping_type'];
		$this->coupon = $this->attributes['coupon'] ?? '';
		$this->graveId = $this->attributes['grave_id'] ?? null;
		$this->comment = $this->attributes['comment'] ?? '';
	}
}