<?php

namespace App\Services\Merchant\Handlers\Incoming\CoinPayments;

use App\Models\Currency\Currency;
use App\Models\Exchange\ExchangeRequest;
use App\Services\CoinPayments\CoinPaymentsService;
use App\Services\CoinPayments\Exceptions\CoinPaymentsApiException;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\ValueObjects\Transaction;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\TransactionInterface;
use JetBrains\PhpStorm\ArrayShape;

class PaymentFormDataHandler
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
        private CoinPaymentsService $coinPaymentsService,
    ) {
    }

    /**
     * @throws CoinPaymentsApiException
     * @throws \JsonException
     */
    #[ArrayShape([
        'address' => "string",
        'qr_code_url' => "string",
        'transaction_id' => "string",
    ])]
    public function handle(ExchangeRequest $exchangeRequest): array
    {
        $transaction = $this->transactionsStorage->get($exchangeRequest->getUniqString());

        if ($transaction === null) {
            $transaction = $this->createTransaction($exchangeRequest);
            $this->transactionsStorage->save($exchangeRequest->getUniqString(), $transaction);
        }

        return [
            'address' => $transaction->getPaymentAddress()->getAddress(),
            'qr_code_url' => $transaction->get('qrCodeUrl'),
            'transaction_id' => $transaction->getTransactionId(),
        ];
    }

    /**
     * @throws CoinPaymentsApiException
     * @throws \JsonException
     */
    private function getPaymentAddress(Currency $currency) : string
    {
        $addressValueObject = $this->coinPaymentsService->getCallbackAddress($currency->code);
        return $addressValueObject->getAddress();
    }

    /**
     * @throws CoinPaymentsApiException|\JsonException
     */
    private function createTransaction(ExchangeRequest $exchangeRequest) : TransactionInterface
    {
        $coinPaymentTransaction = $this->coinPaymentsService->createTransaction(
            $exchangeRequest->given_sum,
            $exchangeRequest->givenCurrency->code,
            $exchangeRequest->givenCurrency->code,
            $exchangeRequest->customer->email,
            $this->getPaymentAddress($exchangeRequest->givenCurrency),
            $exchangeRequest->customer->name,
        );

        $transaction = new Transaction(
            $coinPaymentTransaction->getTransactionId(),
            new PaymentAddress($coinPaymentTransaction->getPaymentAddress()->getAddress()),
            $coinPaymentTransaction->getAmount(),
        );

        $transaction->set('qrCodeUrl', $coinPaymentTransaction->getQrCodeUrl());
        $transaction->set('confirmsNeeded', $coinPaymentTransaction->getConfirmsNeeded());

        return $transaction;
    }
}
