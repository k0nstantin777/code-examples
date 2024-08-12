<?php

namespace App\Services\TelegramBot\Enums;

enum CalculateSumType : string
{
    case GIVEN_CURRENCY = 'given_currency';
    case RECEIVED_CURRENCY = 'received_currency';
}
