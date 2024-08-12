<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages\PaymentFormSteps;

use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;

class ExternalPaymentForm implements PaymentForm
{
    public function __invoke(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        $receivedRequisites = $activeExchangeRequest->getReceivedRequisites();

        $text = '1) ' . __('Follow to the payment gateway [link](:link) and complete the payment', [
            'link' => $activeExchangeRequest->paymentFormData['external_pay_link'] ?? ''
        ]) . "\n";
        $text .= '2) ' . __('You will receive :currency_code on the details', [
            'currency_code' => '*' . $activeExchangeRequest->receivedCurrency->code . '*'
        ]) . ':' . "\n";
        $text .= '*' . $receivedRequisites .'*'. "\n";
        $text .= '3) ' . __('After payment click the button') . ': *"' . __('I paid') . '"*' . "\n";

        return $text;
    }
}