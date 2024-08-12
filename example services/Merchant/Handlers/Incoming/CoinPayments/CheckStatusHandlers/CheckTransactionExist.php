<?php

namespace App\Services\Merchant\Handlers\Incoming\CoinPayments\CheckStatusHandlers;

use App\Services\CoinPayments\CoinPaymentsService;
use App\Services\CoinPayments\Exceptions\CoinPaymentsApiException;
use App\Services\CoinPayments\ValueObjects\TransactionInfo;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        private CoinPaymentsService $coinPaymentsService
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $transactionInfo = $this->getStatus($checkStatusRequest->getExchangeRequest()->payment_transaction_id);

        if ($transactionInfo === null) {
            $checkStatusRequest->set(CheckStatusRequestProperty::TXID, TransactionIdPlaceholder::NOT_FOUND);
            return false;
        }

        $checkStatusRequest->set(
            CheckStatusRequestProperty::TXID,
            $checkStatusRequest->getExchangeRequest()->payment_transaction_id
        );

        $checkStatusRequest->set('transactionInfo', $transactionInfo);
        return true;
    }

    private function getStatus(string $transactionId) : ?TransactionInfo
    {
        try {
            return $this->coinPaymentsService->getTransactionInfo($transactionId);
        } catch (CoinPaymentsApiException|\JsonException) {
            return null;
        }
    }
}
