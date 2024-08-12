<?php

namespace App\Services\Merchant\Handlers\Incoming\BtcHdCryptoWallet\CheckStatusHandlers;

use App\Models\Exchange\ExchangeRequest;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\BlockStream\BlockStreamApiService;
use App\Services\BlockStream\Exceptions\BlockStreamApiException;
use App\Services\BlockStream\ValueObjects\Utxo;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Handlers\Incoming\BtcHdCryptoWallet\PaymentAddressProperty;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckTransactionExist as BaseCheckTransactionExist;

class CheckTransactionExist extends BaseCheckTransactionExist
{
    public function __construct(
        private BlockStreamApiService $blockStreamApiService,
        private PaymentAddressesStorage $paymentAddressesStorage,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();
        $utxoes = $this->findAppropriateUtxoes($exchangeRequest);

        if (count($utxoes) === 0) {
            return false;
        }

        $utxo = $this->getAppropriateUtxo($utxoes, $exchangeRequest);

        $checkStatusRequest->set(
            CheckStatusRequestProperty::TXID,
            optional($utxo)->getTxid() ?? TransactionIdPlaceholder::NOT_FOUND
        );

        if (null === $utxo) {
            return false;
        }

        $checkStatusRequest->set(CheckStatusRequestProperty::TRANSACTION, $utxo);
        return true;
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return Utxo[]
     */
    private function findAppropriateUtxoes(ExchangeRequest $exchangeRequest) : array
    {
        try {
            $paymentAddressFromStorage = $this->paymentAddressesStorage->get($exchangeRequest->payment_address);
            if (null === $paymentAddressFromStorage) {
                return [];
            }

            $utxoesFromAddressStorage = $paymentAddressFromStorage->get(PaymentAddressProperty::UTXOS, []);
            $utxoesFromBlockchain = $this->blockStreamApiService->getAddressUtxos($exchangeRequest->payment_address);

            return array_udiff(
                $utxoesFromBlockchain,
                $utxoesFromAddressStorage,
                fn(Utxo $utxo1, Utxo $utxo2) => strcmp($utxo1->getTxid(), $utxo2->getTxid()),
            );
        } catch (BlockStreamApiException) {
            return [];
        }
    }

    /**
     * @param Utxo[] $utxoes
     */
    private function getAppropriateUtxo(
        array $utxoes,
        ExchangeRequest $exchangeRequest,
    ) : ?Utxo {
        $exchangeRequestFilter = ExchangeRequestFilter::getInstance();

        foreach ($utxoes as $utxo) {
            $duplicateExchangeRequest = $exchangeRequestFilter
                ->refresh()
                ->setPaymentTransactionId($utxo->getTxid())
                ->setGivenMerchantId($exchangeRequest->given_merchant_id)
                ->first();

            if (null === $duplicateExchangeRequest) {
                return $utxo;
            }
        }

        return null;
    }
}
