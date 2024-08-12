<?php

namespace App\Services\Merchant\Handlers\Incoming\PerfectMoney;

use App\Models\Currency\Currency;
use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\Exceptions\MerchantConfigurationException;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\PerfectMoney\Exceptions\PerfectMoneyApiException;
use App\Services\PerfectMoney\PerfectMoneyService;
use JetBrains\PhpStorm\ArrayShape;

class PaymentFormDataHandler
{
    public function __construct(
        private PaymentAddressesStorage $paymentAddressesStorage,
        private PerfectMoneyService $perfectMoneyService,
    ) {
    }

    /**
     * @throws PerfectMoneyApiException
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
            'transaction_id' => TransactionIdPlaceholder::SEARCHING
        ];
    }

    /**
     * @throws PerfectMoneyApiException|MerchantConfigurationException
     */
    private function getPaymentAddress(Currency $currency) : PaymentAddress
    {
        $wallets = $this->perfectMoneyService->getBalance();
        $searchWallet = preg_replace('/\s+/', '', $currency->payment_requisites);

        foreach ($wallets as $wallet) {
            if ($searchWallet === $wallet->getId()) {
                return new PaymentAddress($searchWallet);
            }
        }

        throw new MerchantConfigurationException(
            'Invalid wallet ID: ' . $currency->payment_requisites . ', for merchant Perfect Money'
        );
    }
}
