<?php

namespace App\Services\Exchanger\RequestDTOs;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class PayExchangeRequestRequestDto extends DataTransferObject
{
    #[MapFrom('id')]
    public string $id;

    #[MapFrom('customer_id')]
    public int $customerId;

    #[MapFrom('transaction_id')]
    public string $transactionId;

    #[MapFrom('payment_address')]
    public string $paymentAddress;
}