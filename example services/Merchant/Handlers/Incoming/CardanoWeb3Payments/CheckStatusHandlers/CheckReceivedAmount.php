<?php

namespace App\Services\Merchant\Handlers\Incoming\CardanoWeb3Payments\CheckStatusHandlers;

use App\Services\BlockFrost\ValueObjects\TransactionOutput;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Money\MoneyFloatService;
use App\Services\Money\ValueObjects\MoneyFloat;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckReceivedAmount as BaseCheckReceivedAmount;
use App\Services\BlockFrost\ValueObjects\Transaction;
use JetBrains\PhpStorm\Pure;

class CheckReceivedAmount extends BaseCheckReceivedAmount
{
    public function __construct(
        private MoneyFloatService $moneyFloatService,
        private TransactionsStorage $transactionsStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /** @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        $transactionOutput = $this->getTransactionOutputByAddress($transaction, $exchangeRequest->payment_address);

        if (null === $transactionOutput) {
            return false;
        }

        $receivedAmount = lovelaceToAda($transactionOutput->getAmounts()[0]->getQuantity());
        $checkStatusRequest->set(CheckStatusRequestProperty::RECEIVED_AMOUNT, $receivedAmount);

        if ($receivedAmount > 0 &&
            $this->moneyFloatService->compare(
                new MoneyFloat($receivedAmount),
                new MoneyFloat($exchangeRequest->given_sum)
            ) === -1
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            return false;
        }

        return true;
    }

    #[Pure]
    private function getTransactionOutputByAddress(
        Transaction $transaction,
        string $paymentAddress
    ) : ?TransactionOutput {
        foreach ($transaction->getOutputs() as $transactionOutput) {
            if ($transactionOutput->getAddress() === $paymentAddress &&
                isset($transactionOutput->getAmounts()[0]) &&
                $transactionOutput->getAmounts()[0]->isLovelaceUnit()
            ) {
                return $transactionOutput;
            }
        }

        return null;
    }
}
