<?php


namespace App\Services\Merchant\Handlers\Outgoing\CoinPayments;

use App\Enums\ExchangeAttributeCode;
use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPayoutDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidOutErrorOccurred;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaidOut;
use App\Models\Exchange\ExchangeRequest;
use App\Services\CoinPayments\CoinPaymentsService;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Outgoing\BaseOutgoingHandler;
use Illuminate\Support\Facades\Log;

class CoinPaymentsOutgoingHandler extends BaseOutgoingHandler
{
    public function __construct(
        private CoinPaymentsService $coinPaymentsService,
    ) {
    }

    public function makePayout(ExchangeRequest $exchangeRequest): void
    {
        $transactionId = $this->sendMoney($exchangeRequest);
        $payoutAddress = $exchangeRequest->received_requisites;

        if (null === $transactionId) {
            event(new PaidOutErrorOccurred(
                $exchangeRequest,
                new ExchangeRequestPayoutDto(
                    TransactionIdPlaceholder::NOT_FOUND,
                    $payoutAddress,
                    __('Payment error occurred, please contact support')
                ),
            ));
            return;
        }

        $payoutDto = new ExchangeRequestPayoutDto(
            $transactionId,
            $payoutAddress,
        );

        event(new SuccessPaidOut($exchangeRequest, $payoutDto));
    }

    public function getRequiredExchangeAttributeCodes(): array
    {
        return [
            ExchangeAttributeCode::REQUISITES_RECEIVED_CURRENCY,
        ];
    }

    private function sendMoney(ExchangeRequest $exchangeRequest) : ?string
    {
        try {
            return $this->coinPaymentsService->createWithdrawal(
                $exchangeRequest->received_sum,
                $exchangeRequest->receivedCurrency->code,
                $exchangeRequest->received_requisites,
                'Exchange Request #' . $exchangeRequest->token,
            )->getId();
        } catch (\Exception $e) {
            Log::channel('payouts-log')->error($e->getMessage(), ['exception' => $e]);
            return null;
        }
    }
}
