<?php


namespace App\Services\Merchant\Handlers\Outgoing\WhiteBit;

use App\Enums\ExchangeAttributeCode;
use App\Models\Currency\Currency;
use App\Models\Exchange\ExchangeRequest;
use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPayoutDto;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\PaidOutErrorOccurred;
use App\Modules\Queue\Events\Entities\ExchangeRequest\Transaction\SuccessPaidOut;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Outgoing\BaseOutgoingHandler;
use App\Services\WhiteBit\PrivateApi\Enums\TransactionMethod;
use App\Services\WhiteBit\PrivateApi\Exceptions\WhiteBitApiException;
use App\Services\WhiteBit\PrivateApi\RequestDTOs\HistoryRequestDto;
use App\Services\WhiteBit\PrivateApi\ValueObjects\Transaction;
use App\Services\WhiteBit\PrivateApi\WhiteBitPrivateApiService;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

abstract class WhiteBitOutgoingHandler extends BaseOutgoingHandler
{
    public function __construct(
        protected readonly WhiteBitPrivateApiService $whiteBitApiService,
    ) {
    }

    /**
     * @throws UnknownProperties
     */
    public function makePayout(ExchangeRequest $exchangeRequest): void
    {
        $isTransactionCreated = $this->createTransaction($exchangeRequest);
        $transactionId = TransactionIdPlaceholder::NOT_FOUND;
        $payoutAddress = $exchangeRequest->received_requisites;

        if (false === $isTransactionCreated) {
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

        $transaction = $this->getTransaction($exchangeRequest);

        if ($transaction && !$transaction->hash) {
            $transactionId = TransactionIdPlaceholder::SEARCHING;
        } elseif ($transaction && $transaction->hash) {
            $transactionId = $transaction->hash;
        }

        $payoutDto = new ExchangeRequestPayoutDto(
            $transactionId,
            $payoutAddress,
        );

        event(new SuccessPaidOut($exchangeRequest, $payoutDto));
    }

    abstract protected function createTransaction(ExchangeRequest $exchangeRequest) : bool;

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return Transaction|null
     * @throws UnknownProperties
     */
    protected function getTransaction(ExchangeRequest $exchangeRequest) : ?Transaction
    {
        try {
            $uniqueId = $this->getTransactionUniqueId($exchangeRequest);

            $dto = new HistoryRequestDto(
                unique_id: $uniqueId,
                method: TransactionMethod::WITHDRAW,
                currency: $exchangeRequest->receivedCurrency->code,
            );

            $history = $this->whiteBitApiService->getHistory($dto);

            foreach ($history->records as $transaction) {
                if ($transaction->uniqueId === $uniqueId &&
                    $transaction->method === TransactionMethod::WITHDRAW
                ) {
                    return $transaction;
                }
            }
        } catch (WhiteBitApiException|\JsonException) {
        }

        return null;
    }

    protected function getTransactionUniqueId(ExchangeRequest $exchangeRequest) : string
    {
        return 'withdraw_' . $exchangeRequest->token;
    }

    public function getRequiredExchangeAttributeCodes(): array
    {
        return [
            ExchangeAttributeCode::REQUISITES_RECEIVED_CURRENCY,
        ];
    }

    /**
     * @throws UnknownProperties
     */
    public function findPayoutTransactionId(ExchangeRequest $exchangeRequest) : string
    {
        $transaction = $this->getTransaction($exchangeRequest);

        return $transaction->hash ?? '';
    }

    /**
    * @throws UnknownProperties
    * @throws WhiteBitApiException
    * @throws \JsonException
    */
    public function fetchReservesByCurrency(Currency $currency) : string
    {
        $balances = $this->whiteBitApiService->getAccountBalance($currency->code);

        foreach ($balances as $wallet) {
            if ($wallet->currency !== $currency->code) {
                continue;
            }

            return $wallet->balance;
        }

        return parent::fetchReservesByCurrency($currency);
    }
}
