<?php

namespace App\Services\Merchant\ValueObjects;

interface TransactionInterface
{
    /**
     * @return string
     */
    public function getTransactionId(): string;

    /**
     * @return PaymentAddressInterface
     */
    public function getPaymentAddress(): PaymentAddressInterface;

    /**
     * @return string
     */
    public function getAmount(): string;
}
