<?php


namespace App\Services\Merchant\Handlers\Outgoing\Payeer;

use App\Enums\ExchangeAttributeCode;
use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPayoutDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidOutErrorOccurred;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaidOut;
use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Outgoing\BaseOutgoingHandler;
use App\Services\Payeer\Exceptions\PayeerApiException;
use App\Services\Payeer\PayeerService;
use App\Services\Payeer\RequestDTOs\PayoutDTO;
use Illuminate\Support\Facades\Log;

class PayeerOutgoingHandler extends BaseOutgoingHandler
{
    public function __construct(
        private PayeerService $payeerService,
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

        $exchangeRequestPayoutDto = new ExchangeRequestPayoutDto(
            $transactionId,
            $payoutAddress,
        );

        event(new SuccessPaidOut($exchangeRequest, $exchangeRequestPayoutDto));
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
            $response = $this->payeerService->payoutChecking(
                new PayoutDTO(
                    $exchangeRequest->received_sum,
                    $exchangeRequest->receivedCurrency->code,
                    $exchangeRequest->received_requisites,
                    $exchangeRequest->token
                )
            );

            return (bool) $response;
        } catch (PayeerApiException $e) {
            Log::channel('payouts-log')->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    private function sendMoney(ExchangeRequest $exchangeRequest) : ?string
    {
        try {
            $response = $this->payeerService->payout(
                new PayoutDTO(
                    $exchangeRequest->received_sum,
                    $exchangeRequest->receivedCurrency->code,
                    $exchangeRequest->received_requisites,
                    $exchangeRequest->token
                )
            );

            return $response->getTransactionId();
        } catch (PayeerApiException $e) {
            Log::channel('payouts-log')->error($e->getMessage(), ['exception' => $e]);
            return null;
        }
    }
}
