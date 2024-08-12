<?php

namespace App\Services\Merchant\ValueObjects;

interface PaymentAddressInterface
{
    public function getAddress() : string;
}
