<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages\PaymentFormSteps;

use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;

class InternalPaymentForm implements PaymentForm
{
    public function __invoke(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        $receivedRequisites = $activeExchangeRequest->getReceivedRequisites();

        $text = '1) ' . __('Make a :currency_code transfer using the details', ['currency_code' => '*' . $activeExchangeRequest->givenCurrency->code . '*']) . ':' . "\n";
        $text .= __('Wallet') . ': *' . $activeExchangeRequest->paymentFormData['address'] . "*\n";
        $text .= __('On Sum') . ': *' . $activeExchangeRequest->givenSum . ' ' . $activeExchangeRequest->givenCurrency->code . "*\n";
        $text .= '2) ' . __('You will receive :currency_code on the details', ['currency_code' => '*' . $activeExchangeRequest->receivedCurrency->code . '*']) . ':' . "\n";
        $text .= '*' . $receivedRequisites .'*'. "\n";
        $text .= '3) ' . __('After payment click the button') . ': *"' . __('I paid') . '"*' . "\n";

        return $text;
    }
}