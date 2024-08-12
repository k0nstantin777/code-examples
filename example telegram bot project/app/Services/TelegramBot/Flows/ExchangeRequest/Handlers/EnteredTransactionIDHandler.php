<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

class EnteredTransactionIDHandler extends ExchangeRequestProcessingHandler
{
    public function handle(): void
    {
        $value = $this->update->message->text;

        $paymentFormData = $this->exchangeRequest->getPaymentFormData();

        $paymentFormData['transaction_id'] = $value;

        $this->exchangeRequest->setPaymentFormData($paymentFormData);

        parent::handle();
    }
}
