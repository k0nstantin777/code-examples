<?php


namespace App\Services\Merchant\Handlers\Outgoing\AdvancedCash;

use App\Enums\ExchangeAttributeCode;
use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPayoutDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidOutErrorOccurred;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaidOut;
use App\Models\Exchange\ExchangeRequest;
use App\Services\AdvancedCash\AdvancedCashService;
use App\Services\AdvancedCash\Exceptions\AdvancedCashApiException;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Outgoing\BaseOutgoingHandler;
use Illuminate\Support\Facades\Log;

class AdvancedCashOutgoingHandler extends BaseOutgoingHandler
{
    public function __construct(
        private AdvancedCashService $advancedCashService,
    ) {
    }

    public function makePayout(ExchangeRequest $exchangeRequest): void
    {
        $transactionId = TransactionIdPlaceholder::NOT_FOUND;
        $payoutAddress = $exchangeRequest->received_requisites;

        if (false === $this->validateSendMoney($exchangeRequest)) {
            event(new PaidOutErrorOccurred(
                $exchangeRequest,
                new ExchangeRequestPayoutDto(
                    $transactionId,
                    $payoutAddress,
                    __('Payment error occurred, please contact support')
                ),
            ));
            return;
        }

        $transactionId = $this->sendMoney($exchangeRequest);

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

    private function validateSendMoney(ExchangeRequest $exchangeRequest) : bool
    {
        try {
            return (bool) $this->advancedCashService->validateSendMoney(
                $exchangeRequest->received_sum,
                $exchangeRequest->received_requisites,
                $exchangeRequest->receivedCurrency->code,
            );
        } catch (AdvancedCashApiException $exception) {
            Log::channel('payouts-log')->error($exception->getMessage(), ['exception' => $exception]);
            return false;
        }
    }

    private function sendMoney(ExchangeRequest $exchangeRequest) : ?string
    {
        try {
            return $this->advancedCashService->sendMoney(
                $exchangeRequest->received_sum,
                $exchangeRequest->received_requisites,
                $exchangeRequest->receivedCurrency->code,
            )->return;
        } catch (AdvancedCashApiException $exception) {
            Log::channel('payouts-log')->error($exception->getMessage(), ['exception' => $exception]);
            return null;
        }
    }
}
