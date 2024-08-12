<?php


namespace App\Services\Merchant\Handlers\Incoming;

use App\Enums\CurrencyLabel;
use App\Models\Exchange\ExchangeRequest;
use JetBrains\PhpStorm\ArrayShape;

class ManualIncomingHandler extends BaseIncomingHandler
{

    #[ArrayShape(['address' => "null|string", 'transaction_id' => "null|string"])]
    public function getPaymentFormData(ExchangeRequest $exchangeRequest): array
    {
        $formData = [
            'address' => $exchangeRequest->givenCurrency->payment_requisites,
        ];

        if ($exchangeRequest->givenCurrency->label === CurrencyLabel::BANK) {
            $formData['transaction_id'] = CurrencyLabel::BANK;
        }

        return $formData;
    }

    public function checkStatus(ExchangeRequest $exchangeRequest): void
    {
        // For manual merchant nothing to do
    }
}
