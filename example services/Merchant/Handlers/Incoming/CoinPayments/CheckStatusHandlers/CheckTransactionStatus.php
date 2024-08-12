<?php

namespace App\Services\Merchant\Handlers\Incoming\CoinPayments\CheckStatusHandlers;

use App\Modules\Queue\Entities\ExchangeRequest\DataTransferObjects\ExchangeRequestPaymentDto;
use App\Services\CoinPayments\ValueObjects\TransactionInfo;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionStatus as BaseCheckTransactionStatus;
use App\Services\Merchant\ValueObjects\Transaction;

class CheckTransactionStatus extends BaseCheckTransactionStatus
{
    private const COMPLETED_STATUS = 100;

    public function __construct(
        private TransactionsStorage $transactionsStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        /* @var TransactionInfo $transactionInfo */
        $transactionInfo = $checkStatusRequest->get('transactionInfo');

        /* @var Transaction $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);

        if ($transactionInfo->getStatus() === self::COMPLETED_STATUS &&
            $transaction->get('confirmsNeeded') === $transactionInfo->getReceivedConfirms()
        ) {
            $this->transactionsStorage->forget($exchangeRequest->getUniqString());
            return true;
        }

        return false;
    }
}
