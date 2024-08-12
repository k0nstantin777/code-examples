<?php


namespace App\Services\Merchant\Handlers\Incoming\CoinPayments;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Exceptions\PaymentFormCreateException;
use App\Services\Merchant\Handlers\Incoming\BaseIncomingHandler;
use JetBrains\PhpStorm\ArrayShape;

class CoinPaymentsIncomingHandler extends BaseIncomingHandler
{
    public function __construct(
        private CheckStatusHandler $checkStatusHandler,
        private PaymentFormDataHandler $paymentFormDataHandler,
    ) {
    }

    /**
     * @throws PaymentFormCreateException
     */
    #[ArrayShape([
        'address' => "string",
        'qr_code_url' => "string",
        'transaction_id' => "string",
    ])]
    public function getPaymentFormData(ExchangeRequest $exchangeRequest): array
    {
        try {
            return $this->paymentFormDataHandler->handle($exchangeRequest);
        } catch (\Exception) {
            throw new PaymentFormCreateException(
                400,
                __('Error creating a payment form, please contact support')
            );
        }
    }

    public function checkStatus(ExchangeRequest $exchangeRequest) : void
    {
        $this->checkStatusHandler->handle($exchangeRequest);
    }
}
