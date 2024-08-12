<?php


namespace App\Services\Merchant\Handlers\Incoming\EasyTransfer;

use App\Enums\ExchangeAttributeCode;
use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Exceptions\PaymentFormCreateException;
use App\Services\Merchant\Handlers\Incoming\BaseIncomingHandler;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class EasyTransferIncomingHandler extends BaseIncomingHandler
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
        'transaction_id' => "string",
        'qr_code_url' => 'string',
        'external_pay_link' => 'string',
        'component' => 'string'
    ])]
    public function getPaymentFormData(ExchangeRequest $exchangeRequest): array
    {
        try {
            return $this->paymentFormDataHandler->handle($exchangeRequest);
        } catch (\Exception $e) {
            Log::channel('easytransfer-log')->error($e->getMessage(), ['exception' => $e]);
            throw new PaymentFormCreateException();
        }
    }

    public function checkStatus(ExchangeRequest $exchangeRequest) : void
    {
        $this->checkStatusHandler->handle($exchangeRequest);
    }

    public function getRequiredExchangeAttributeCodes(): array
    {
        return [
            ExchangeAttributeCode::REQUISITES_GIVEN_CURRENCY,
        ];
    }
}
