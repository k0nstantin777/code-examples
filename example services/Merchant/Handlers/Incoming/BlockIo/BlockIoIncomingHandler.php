<?php


namespace App\Services\Merchant\Handlers\Incoming\BlockIo;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Exceptions\PaymentFormCreateException;
use App\Services\Merchant\Handlers\Incoming\BaseIncomingHandler;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class BlockIoIncomingHandler extends BaseIncomingHandler
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
        } catch (\Exception $e) {
            Log::channel('blockio-log')->error($e->getMessage(), ['exception' => $e]);
            throw new PaymentFormCreateException();
        }
    }

    public function checkStatus(ExchangeRequest $exchangeRequest) : void
    {
        $this->checkStatusHandler->handle($exchangeRequest);
    }
}
