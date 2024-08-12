<?php

namespace App\Services\Merchant\Handlers\Incoming\CardanoWeb3Payments\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\BlockFrost\BlockFrostTransactionService;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;
use App\Services\BlockFrost\ValueObjects\Transaction;
use JetBrains\PhpStorm\Pure;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        private BlockFrostTransactionService $blockFrostTransactionService
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $transaction = $this->getTransaction($exchangeRequest);

        $checkStatusRequest->set(
            CheckStatusRequestProperty::TXID,
            optional($transaction)->getTxHash() ?? TransactionIdPlaceholder::NOT_FOUND
        );

        if (null === $transaction) {
            return false;
        }

        $checkStatusRequest->set(CheckStatusRequestProperty::TRANSACTION, $transaction);
        return true;
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return Transaction|null
     */
    private function getTransaction(ExchangeRequest $exchangeRequest) : ?Transaction
    {
        try {
            $transaction = $this->blockFrostTransactionService->getByHash($exchangeRequest->payment_transaction_id);

            if ($this->isValidTransaction($transaction, $exchangeRequest)) {
                return $transaction;
            }
        } catch (\Exception $e) {
            // Nothing to do
        }

        return null;
    }

    private function isValidTransaction(Transaction $transaction, ExchangeRequest $exchangeRequest) : bool
    {
        return strtolower($transaction->getTxHash()) === strtolower($exchangeRequest->payment_transaction_id) &&
            $this->hasPaymentAddress($transaction, $exchangeRequest->payment_address) &&
            $transaction->getCreatedAt()->greaterThan($exchangeRequest->created_at) &&
            false === $this->hasDuplicate($exchangeRequest);
    }

    #[Pure]
    private function hasPaymentAddress(Transaction $transaction, string $paymentAddress) : bool
    {
        foreach ($transaction->getOutputs() as $output) {
            if ($output->getAddress() === $paymentAddress) {
                return true;
            }
        }

        return false;
    }

    private function hasDuplicate(ExchangeRequest $exchangeRequest) : bool
    {
        $exchangeRequestFilter = ExchangeRequestFilter::getInstance();

        $exchangeRequests = $exchangeRequestFilter
            ->refresh()
            ->setPaymentTransactionId($exchangeRequest->payment_transaction_id)
            ->setGivenMerchantId($exchangeRequest->given_merchant_id)
            ->get();

        return $exchangeRequests->count() > 1;
    }
}
