<?php


namespace App\Services\Merchant\Handlers\Incoming;

use App\Enums\CurrencyLabel;
use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPaymentDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaid;
use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Exceptions\PaymentFormCreateException;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class BalanceIncomingHandler extends BaseIncomingHandler
{
    #[ArrayShape([
        'address' => "string",
        'transaction_id' => "string",
        'component' => 'string'
    ])]
    public function getPaymentFormData(ExchangeRequest $exchangeRequest): array
    {
        if (false === $this->validate($exchangeRequest)) {
            Log::channel('payouts-log')->error(
                'Unsupported currency label for Incoming Balance Merchant: ' .
                $exchangeRequest->givenCurrency->label
            );

            throw new PaymentFormCreateException();
        }

        return [
            'address' => 'balance',
            'transaction_id' => 'balance_payment_' . $exchangeRequest->token,
            'component' => 'internal_balance_payment_form'
        ];
    }

    public function checkStatus(ExchangeRequest $exchangeRequest): void
    {
        if ($exchangeRequest->payment_transaction_id && $exchangeRequest->payment_address) {
            event(new SuccessPaid(
                $exchangeRequest,
                new ExchangeRequestPaymentDto(
                    'balance_payment_' . $exchangeRequest->token
                )
            ));
        }
    }

    private function validate(ExchangeRequest $exchangeRequest) : bool
    {
        return $exchangeRequest->givenCurrency->label === CurrencyLabel::BALANCE;
    }
}
