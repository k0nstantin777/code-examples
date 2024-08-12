<?php


namespace App\Services\Merchant\Handlers\Incoming\WhiteBit\CryptoCurrency;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Exceptions\PaymentFormCreateException;
use App\Services\Merchant\Handlers\Incoming\BaseIncomingHandler;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class WhiteBitCryptoCurrencyIncomingHandler extends BaseIncomingHandler
{
    public function __construct(
        private readonly CheckStatusHandler $checkStatusHandler,
        private readonly PaymentFormDataHandler $paymentFormDataHandler,
    ) {
    }

    /**
     * @throws PaymentFormCreateException
     */
    #[ArrayShape([
        'address' => "string",
        'transaction_id' => "string",
        'qr_code_url' => 'string',
    ])]
    public function getPaymentFormData(ExchangeRequest $exchangeRequest): array
    {
        try {
            return $this->paymentFormDataHandler->handle($exchangeRequest);
        } catch (\Exception $e) {
            Log::channel('whitebit-log')->error($e->getMessage(), ['exception' => $e]);
            throw new PaymentFormCreateException();
        }
    }

    public function checkStatus(ExchangeRequest $exchangeRequest) : void
    {
        $this->checkStatusHandler->handle($exchangeRequest);
    }
}
