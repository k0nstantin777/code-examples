<?php


namespace App\Services\Merchant\Handlers\Outgoing\BlockIo;

use App\Enums\ExchangeAttributeCode;
use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPayoutDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidOutErrorOccurred;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaidOut;
use App\Models\Exchange\ExchangeRequest;
use App\Services\BlockIO\BlockIoService;
use App\Services\BlockIO\ValueObjects\PayoutTransaction;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Outgoing\BaseOutgoingHandler;
use Illuminate\Support\Facades\Log;

class BlockIoOutgoingHandler extends BaseOutgoingHandler
{
    public function __construct(
        private BlockIoService $blockIoService,
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

    private function createTransaction(ExchangeRequest $exchangeRequest) : ?PayoutTransaction
    {
        try {
            $preparedTransactionResponse = $this->blockIoService->prepareTransaction(
                $exchangeRequest->receivedCurrency->code,
                [$exchangeRequest->received_requisites],
                [$exchangeRequest->received_sum]
            );

            $signedTransaction = $this->blockIoService->createAndSignTransaction(
                $exchangeRequest->receivedCurrency->code,
                $preparedTransactionResponse
            );

            return $this->blockIoService->submitTransaction(
                $exchangeRequest->receivedCurrency->code,
                $signedTransaction
            );
        } catch (\Exception $e) {
            Log::channel('payouts-log')->error($e->getMessage(), ['exception' => $e]);
            return null;
        }
    }
}
