<?php

namespace App\Services\Exchanger\Enums;

enum ExchangeRequestStatus : string
{
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PAYMENT_VERIFICATION = 'payment_verification';
    case PAID = 'paid';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
}
