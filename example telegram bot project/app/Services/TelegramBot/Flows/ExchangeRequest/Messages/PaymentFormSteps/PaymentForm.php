<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages\PaymentFormSteps;

use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;

interface PaymentForm
{
    public function __invoke(ActiveExchangeRequest $activeExchangeRequest) : string;
}