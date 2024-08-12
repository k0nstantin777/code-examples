<?php

namespace App\Services\Merchant\Handlers\Incoming\BlockIo\CheckStatusHandlers;

use App\Models\Currency\Currency;
use App\Services\BlockIO\BlockIoService;
use App\Services\BlockIO\Exceptions\BlockIoApiException;
use App\Services\BlockIO\ValueObjects\PaymentAddress;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckAddressExist as BaseCheckAddressExist;

class CheckAddressExist extends BaseCheckAddressExist
{
    public function __construct(
        private BlockIoService $blockIoService,
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $exchangeRequest = $checkStatusRequest->getExchangeRequest();

        $paymentAddressBalance = $this->getPaymentAddressBalance(
            $exchangeRequest->givenCurrency,
            $exchangeRequest->payment_address
        );

        return $paymentAddressBalance !== null;
    }

    private function getPaymentAddressBalance(Currency $currency, string $address) : ?PaymentAddress
    {
        try {
            return $this->blockIoService->getSingleAddressBalance($currency->code, $address);
        } catch (BlockIoApiException|\JsonException) {
            return null;
        }
    }
}
