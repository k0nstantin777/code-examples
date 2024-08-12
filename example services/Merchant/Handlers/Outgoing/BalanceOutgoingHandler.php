<?php


namespace App\Services\Merchant\Handlers\Outgoing;

use App\Modules\Admin\Entities\Customer\DataTransferObjects\CustomerBalanceEventDto;
use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPayoutDto;
use App\Enums\CurrencyLabel;
use App\Enums\CustomerBalanceChangeType;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidOutErrorOccurred;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaidOut;
use App\Models\Exchange\ExchangeRequest;
use App\Modules\Admin\Entities\Customer\Services\CustomerBalanceEventService;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\Pure;

class BalanceOutgoingHandler extends BaseOutgoingHandler
{
    #[Pure]
    public function __construct(
        private CustomerBalanceEventService $customerBalanceEventService,
    ) {
    }

    public function makePayout(ExchangeRequest $exchangeRequest): void
    {
        $payoutAddress = 'balance';
        $payoutTxId = 'balance_payout_' . $exchangeRequest->token;

        if ($exchangeRequest->receivedCurrency->label !== CurrencyLabel::BALANCE) {
            Log::error(
                'Unsupported currency label for Outgoing Balance Merchant: ' .
                $exchangeRequest->givenCurrency->label
            );

            event(new PaidOutErrorOccurred(
                $exchangeRequest,
                new ExchangeRequestPayoutDto(
                    $payoutTxId,
                    $payoutAddress,
                    __('Payment error occurred, please contact support')
                ),
            ));
            return;
        }

        $customerBalanceEventServiceDto = new CustomerBalanceEventDto(
            $exchangeRequest->customer->id,
            $exchangeRequest->received_sum,
            CustomerBalanceChangeType::EXCHANGE(),
        );

        $customerBalanceEventServiceDto->setDescription('Payout exchange request №' . $exchangeRequest->token);

        $this->customerBalanceEventService
            ->createOrUpdate($customerBalanceEventServiceDto);

        $payoutDto = new ExchangeRequestPayoutDto(
            $payoutTxId,
            $payoutAddress,
        );

        event(new SuccessPaidOut($exchangeRequest, $payoutDto));
    }
}
