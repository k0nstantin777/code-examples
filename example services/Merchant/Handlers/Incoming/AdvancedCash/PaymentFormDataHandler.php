<?php

namespace App\Services\Merchant\Handlers\Incoming\AdvancedCash;

use App\Models\Currency\Currency;
use App\Models\Exchange\ExchangeRequest;
use App\Services\AdvancedCash\AdvancedCashService;
use App\Services\AdvancedCash\Exceptions\AdvancedCashApiException;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\Exceptions\MerchantConfigurationException;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use JetBrains\PhpStorm\ArrayShape;

class PaymentFormDataHandler
{
    public function __construct(
        private PaymentAddressesStorage $paymentAddressesStorage,
        private AdvancedCashService $advancedCashService,
    ) {
    }

    /**
     * @throws AdvancedCashApiException
     * @throws MerchantConfigurationException
     */
    #[ArrayShape([
        'address' => "string",
        'transaction_id' => "string",
    ])]
    public function handle(ExchangeRequest $exchangeRequest): array
    {
        $address = $this->paymentAddressesStorage->get($exchangeRequest->getUniqString());

        if ($address === null) {
            $address = $this->getPaymentAddress($exchangeRequest->givenCurrency);

            $this->paymentAddressesStorage->save($exchangeRequest->getUniqString(), $address);
        }

        return [
            'address' => $address->getAddress(),
            'transaction_id' => TransactionIdPlaceholder::SEARCHING,
        ];
    }

    /**
     * @throws AdvancedCashApiException|MerchantConfigurationException
     */
    private function getPaymentAddress(Currency $currency) : PaymentAddress
    {
        $balances = $this->advancedCashService->getBalances();
        $searchWallet = preg_replace('/\s+/', '', $currency->payment_requisites);

        foreach ($balances->return as $wallet) {
            if ($searchWallet === $wallet->id) {
                return new PaymentAddress($searchWallet);
            }
        }

        throw new MerchantConfigurationException(
            'Invalid wallet ID: ' . $currency->payment_requisites . ', for merchant Advanced Cash'
        );
    }
}
