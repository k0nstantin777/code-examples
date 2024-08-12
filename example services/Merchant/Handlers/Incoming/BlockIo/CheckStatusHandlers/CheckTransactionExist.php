<?php

namespace App\Services\Merchant\Handlers\Incoming\BlockIo\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\BlockIO\BlockIoService;
use App\Services\BlockIO\Exceptions\BlockIoApiException;
use App\Services\BlockIO\RequestDTOs\GetTransactionsDto;
use App\Services\BlockIO\ValueObjects\ReceivedTransaction;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        private BlockIoService $blockIoService
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $transactions = $this->findAppropriateTransactions($exchangeRequest);

        if (count($transactions) === 0) {
            return false;
        }

        $transaction = $this->getAppropriateTransaction($transactions, $exchangeRequest);

        $checkStatusRequest->set(
            CheckStatusRequestProperty::TXID,
            optional($transaction)->getId() ?? TransactionIdPlaceholder::NOT_FOUND
        );

        if (null === $transaction) {
            return false;
        }

        $checkStatusRequest->set(CheckStatusRequestProperty::TRANSACTION, $transaction);
        return true;
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return ReceivedTransaction[]
     */
    private function findAppropriateTransactions(ExchangeRequest $exchangeRequest) : array
    {
        try {
            $requestDto = new GetTransactionsDto();
            $requestDto->setAddresses([$exchangeRequest->payment_address]);

            $transactionHistory = $this->blockIoService->getReceivedTransactions(
                $exchangeRequest->givenCurrency->code,
                $requestDto
            );

            $transactions = [];
            foreach ($transactionHistory as $receivedTransaction) {
                if ($exchangeRequest->created_at->lessThan($receivedTransaction->getCreatedDate())) {
                    $transactions[] = $receivedTransaction;
                }
            }

            return $transactions;
        } catch (BlockIoApiException|\JsonException) {
            return [];
        }
    }

    /**
     * @param ReceivedTransaction[] $transactions
     */
    private function getAppropriateTransaction(
        array $transactions,
        ExchangeRequest $exchangeRequest
    ) : ?ReceivedTransaction {
        $exchangeRequestFilter = ExchangeRequestFilter::getInstance();

        foreach ($transactions as $transaction) {
            $duplicateExchangeRequest = $exchangeRequestFilter
                ->refresh()
                ->setPaymentTransactionId($transaction->getId())
                ->setGivenMerchantId($exchangeRequest->given_merchant_id)
                ->first();

            if (null === $duplicateExchangeRequest) {
                return $transaction;
            }
        }

        return null;
    }
}
