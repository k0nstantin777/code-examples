<?php

namespace App\Services\Merchant\Handlers\Incoming\CardanoWeb3Payments;

use App\Models\Exchange\ExchangeRequest;
use App\Modules\Queue\Entities\ExchangeRequest\Criterion\HasLastPaymentAddressByMerchantCriteria;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\ValueObjects\Transaction;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;

class PaymentFormDataHandler
{
    public function __construct(
        private PaymentAddressesStorage $paymentAddressesStorage,
        private TransactionsStorage $transactionsStorage,
    ) {
    }

    #[ArrayShape([
        'address' => "string",
        'transaction_id' => "string",
        'component' => "string",
        'bfak' => "string", // Blockfrost Api key
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
            'transaction_id' => $transaction->getTransactionId(),
            'component' => 'cardano_web3_payment_form',
            'bfak' => config('services.blockfrost.api_key'),
        ];
    }

    private function getPaymentAddress(ExchangeRequest $exchangeRequest) : PaymentAddress
    {
        $addresses = collect(config('services.cardano_web3_payments.addresses_pool', []));

        if ($addresses->count() === 0) {
            $newAddress = $exchangeRequest->givenCurrency->payment_requisites;
            $paymentAddress = new PaymentAddress($newAddress);
        } else {
            $paymentAddress =  $this->getAvailableAddress(
                $exchangeRequest,
                $addresses,
            );
        }

        $this->paymentAddressesStorage->save($paymentAddress->getAddress(), $paymentAddress);
        return $paymentAddress;
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @param Collection $addresses
     * @return PaymentAddress
     */
    private function getAvailableAddress(ExchangeRequest $exchangeRequest, Collection $addresses) : PaymentAddress
    {
        $lastUsedAddress = $this->getLastUsedAddress($exchangeRequest);
        $lastPaymentAddressIndex = null;

        if ($lastUsedAddress) {
            foreach ($addresses as $index => $address) {
                if ($lastUsedAddress === $address) {
                    $lastPaymentAddressIndex = $index;
                    break;
                }
            }
        }

        $startSearch = $lastPaymentAddressIndex === null ? 0 : ++$lastPaymentAddressIndex;

        $paymentAddress = null;

        if ($startSearch > 0) {
            $paymentAddress = $this->searchAvailableAddressInStorage($addresses, $startSearch);
        }

        if (null === $paymentAddress) {
            $paymentAddress = $this->searchAvailableAddressInStorage($addresses, 0);
        }

        if (null === $paymentAddress) {
            $paymentAddress = $addresses->first();
        }

        return new PaymentAddress($paymentAddress);
    }

    private function searchAvailableAddressInStorage(Collection $addresses, int $start) : ?string
    {
        $paymentAddress = null;
        for ($i = $start; $i < $addresses->count(); $i++) {
            $address = $addresses->get($i);

            $busyPaymentAddress = $this->paymentAddressesStorage->get($address);

            if ($busyPaymentAddress === null) {
                $paymentAddress = $address;
                break;
            }
        }

        return $paymentAddress;
    }

    private function getLastUsedAddress(ExchangeRequest $exchangeRequest) : string
    {
        $lastExchangeRequestWithSameMerchant = ExchangeRequestFilter::getInstance()
            ->pushCriteria(new HasLastPaymentAddressByMerchantCriteria($exchangeRequest->given_merchant_id))
            ->addSortBy('created_at', 'desc')
            ->first();

        return $lastExchangeRequestWithSameMerchant->payment_address ?? '';
    }

    private function createTransaction(ExchangeRequest $exchangeRequest) : Transaction
    {
        $paymentAddress = $this->getPaymentAddress($exchangeRequest);

        return new Transaction(
            TransactionIdPlaceholder::SEARCHING,
            $paymentAddress,
            $exchangeRequest->given_sum,
        );
    }
}
