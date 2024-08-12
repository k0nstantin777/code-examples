<?php

namespace App\Services\Merchant\Handlers\Incoming\Payeer;

use App\Models\Currency\Currency;
use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\Exceptions\MerchantConfigurationException;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Payeer\Exceptions\PayeerApiException;
use App\Services\Payeer\PayeerService;
use JetBrains\PhpStorm\ArrayShape;

final class PaymentFormDataHandler
{
    public function __construct(
        private PaymentAddressesStorage $paymentAddressesStorage,
        private PayeerService $payeerService,
    ) {
    }

    /**
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
     * @throws MerchantConfigurationException
     */
    private function getPaymentAddress(Currency $currency) : PaymentAddress
    {
        try {
            $checkAccount = $this->payeerService->checkUser($currency->payment_requisites);

            if ($checkAccount) {
                return new PaymentAddress($currency->payment_requisites);
            }
        } catch (PayeerApiException) {
            throw new MerchantConfigurationException(
                'Invalid account: ' . $currency->payment_requisites . ', for merchant Payeer'
            );
        }
    }
}
