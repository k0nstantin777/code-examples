<?php


namespace App\Services\Merchant\Handlers\Outgoing\EasyTransfer;

use App\Enums\ExchangeAttributeCode;
use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPayoutDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidOutErrorOccurred;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaidOut;
use App\Models\Exchange\ExchangeRequest;
use App\Services\EasyTransfer\EasyTransferApiService;
use App\Services\EasyTransfer\RequestDTOs\CreatePayoutDto;
use App\Services\EasyTransfer\ValueObjects\Payout;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Outgoing\BaseOutgoingHandler;
use Illuminate\Support\Facades\Log;

class EasyTransferOutgoingHandler extends BaseOutgoingHandler
{
    public function __construct(
        private EasyTransferApiService $easyTransferApiService,
    ) {
    }

    public function makePayout(ExchangeRequest $exchangeRequest): void
    {
        $transaction = $this->createTransaction($exchangeRequest);
        $transactionId = TransactionIdPlaceholder::NOT_FOUND;
        $payoutAddress = $exchangeRequest->received_requisites;

        if (null === $transaction) {
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

        $transactionId = $transaction->getId();

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

    private function createTransaction(ExchangeRequest $exchangeRequest) : ?Payout
    {
        try {
            $dto = new CreatePayoutDto(
                $exchangeRequest->received_requisites,
                $exchangeRequest->received_sum,
            );
            $dto->setExternalId($exchangeRequest->token);

            $payoutTransaction = $this->easyTransferApiService->createPayout($dto);
            $this->easyTransferApiService->payoutPay($payoutTransaction->getId());

            return $payoutTransaction;
        } catch (\Exception $e) {
            Log::channel('payouts-log')->error($e->getMessage(), ['exception' => $e]);
            return null;
        }
    }
}
