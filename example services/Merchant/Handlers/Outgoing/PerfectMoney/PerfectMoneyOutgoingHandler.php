<?php


namespace App\Services\Merchant\Handlers\Outgoing\PerfectMoney;

use App\Enums\ExchangeAttributeCode;
use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPayoutDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidOutErrorOccurred;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaidOut;
use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Outgoing\BaseOutgoingHandler;
use App\Services\PerfectMoney\Exceptions\PerfectMoneyApiException;
use App\Services\PerfectMoney\RequestDTOs\PayoutDto;
use App\Services\PerfectMoney\PerfectMoneyService;
use Illuminate\Support\Facades\Log;

class PerfectMoneyOutgoingHandler extends BaseOutgoingHandler
{
    public function __construct(
        private PerfectMoneyService $perfectMoneyService,
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
            $response = $this->perfectMoneyService->verifyTransferFund(
                $this->makePayoutDto($exchangeRequest)
            );

            return (bool) $response;
        } catch (PerfectMoneyApiException $e) {
            Log::channel('payouts-log')->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    private function sendMoney(ExchangeRequest $exchangeRequest) : ?string
    {
        try {
            $response = $this->perfectMoneyService->transferFund(
                $this->makePayoutDto($exchangeRequest)
            );

            return $response->getBatch();
        } catch (PerfectMoneyApiException $e) {
            Log::channel('payouts-log')->error($e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    private function makePayoutDto(ExchangeRequest $exchangeRequest) : PayoutDto
    {
        $payoutDto = new PayoutDto(
            $exchangeRequest->received_sum,
            $exchangeRequest->receivedCurrency->payment_requisites,
            $exchangeRequest->received_requisites,
        );

        $payoutDto->setPaymentId($exchangeRequest->token);

        return $payoutDto;
    }
}
