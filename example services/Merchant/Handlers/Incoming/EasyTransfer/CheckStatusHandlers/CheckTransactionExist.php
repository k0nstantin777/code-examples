<?php

namespace App\Services\Merchant\Handlers\Incoming\EasyTransfer\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Services\EasyTransfer\EasyTransferApiService;
use App\Services\EasyTransfer\ValueObjects\InvoiceStatus;
use App\Services\EasyTransfer\Exceptions\EasyTransferApiException;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        private EasyTransferApiService $easyTransferApiService
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $transaction = $this->getTransaction($exchangeRequest);

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
     * @return InvoiceStatus|null
     */
    private function getTransaction(ExchangeRequest $exchangeRequest) : ?InvoiceStatus
    {
        try {
            return $this->easyTransferApiService->getInvoiceStatus(
                $exchangeRequest->payment_transaction_id
            );
        } catch (EasyTransferApiException|\JsonException) {
            return null;
        }
    }
}
