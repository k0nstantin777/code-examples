<?php

namespace App\Services\Merchant\ValueObjects;

class Transaction implements TransactionInterface
{
    protected array $attributes;

    public function __construct(
        protected string $transactionId,
        protected PaymentAddressInterface $paymentAddress,
        protected string $amount,
    ) {
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return PaymentAddressInterface
     */
    public function getPaymentAddress(): PaymentAddressInterface
    {
        return $this->paymentAddress;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function set(string $key, mixed $value) : void
    {
        $this->attributes[$key] = $value;
    }

    public function all() : array
    {
        return $this->attributes;
    }
}
