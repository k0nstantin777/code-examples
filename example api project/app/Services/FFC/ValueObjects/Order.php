<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Order extends BaseValueObject
{
    private int $id;
    private Carbon $createdDate;
    private string $orderNumber;
    private string $status;
    private string $price;
    private string $tax;
    private string $rebate;
    private string $comment;
    private OrderCustomer $customer;
    private ?OrderAddress $deliveryAddress = null;
    private ?OrderAddress $paymentAddress = null;
    private ?OrderService $deliveryService = null;
    private ?OrderService $paymentService = null;

    /**
     * @var Collection|Product[]
     */
    private Collection $products;
    private ?AccountGrave $grave = null;
    private ?OrderCoupon $coupon = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getTax(): string
    {
        return $this->tax;
    }

    /**
     * @return string
     */
    public function getRebate(): string
    {
        return $this->rebate;
    }

    /**
     * @return Carbon
     */
    public function getCreatedDate(): Carbon
    {
        return $this->createdDate;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @return OrderAddress|null
     */
    public function getDeliveryAddress(): ?OrderAddress
    {
        return $this->deliveryAddress;
    }

    /**
     * @return OrderAddress|null
     */
    public function getPaymentAddress(): ?OrderAddress
    {
        return $this->paymentAddress;
    }

    /**
     * @return OrderService|null
     */
    public function getDeliveryService(): ?OrderService
    {
        return $this->deliveryService;
    }

    /**
     * @return OrderService|null
     */
    public function getPaymentService(): ?OrderService
    {
        return $this->paymentService;
    }

    /**
     * @return Product[]|Collection
     */
    public function getProducts(): array|Collection
    {
        return $this->products;
    }

    /**
     * @return AccountGrave|null
     */
    public function getGrave(): ?AccountGrave
    {
        return $this->grave;
    }

    /**
     * @return OrderCoupon|null
     */
    public function getCoupon(): ?OrderCoupon
    {
        return $this->coupon;
    }

    /**
     * @return OrderCustomer
     */
    public function getCustomer(): OrderCustomer
    {
        return $this->customer;
    }

    protected function getSchema(): array
    {
        return [
            'id',
            'created_date',
            'order_number',
            'status',
            'price',
            'rebate',
            'tax',
            'comment',
            'customer',
            '?delivery_address',
            '?payment_address',
            '?delivery_service',
            '?payment_service',
            '?products',
            '?grave',
            '?coupon',
        ];
    }

    /**
     * @throws InvalidSchemaException
     */
    protected function map(): void
    {
        $this->id = $this->attributes['id'];
        $this->createdDate = Carbon::parse($this->attributes['created_date']);
        $this->orderNumber = $this->attributes['order_number'];
        $this->price = $this->attributes['price'];
        $this->tax = $this->attributes['tax'];
        $this->rebate = $this->attributes['rebate'];
        $this->status = $this->attributes['status'];
        $this->comment = $this->attributes['comment'];
        $this->customer = new OrderCustomer($this->attributes['customer']);
        $this->deliveryAddress = isset($this->attributes['delivery_address']) && filled($this->attributes['delivery_address']) ?
            new OrderAddress($this->attributes['delivery_address']) :  null;

        $this->paymentAddress = isset($this->attributes['payment_address']) && filled($this->attributes['payment_address']) ?
            new OrderAddress($this->attributes['payment_address']) :  null;

        $this->deliveryService = isset($this->attributes['delivery_service']) && filled($this->attributes['delivery_service']) ?
            new OrderService($this->attributes['delivery_service']) :  null;

        $this->paymentService = isset($this->attributes['payment_service']) && filled($this->attributes['payment_service']) ?
            new OrderService($this->attributes['payment_service']) :  null;

        $this->grave = isset($this->attributes['grave']) && filled($this->attributes['grave']) ?
             AccountGrave::makeFromData($this->attributes['grave']) :  null;

        $this->coupon = isset($this->attributes['coupon']) && filled($this->attributes['coupon']) ?
            new OrderCoupon($this->attributes['coupon']) :  null;

        $this->products = $this->fillProducts();
    }

    /**
     * @throws InvalidSchemaException
     */
    private function fillProducts(): Collection
    {
        $results = collect();

        foreach (Arr::wrap($this->attributes['products'] ?? []) as $productData) {
            $results->push(new OrderProduct($productData));
        }

        return $results;
    }
}
