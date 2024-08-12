<?php

namespace App\Services\Merchant\Handlers\Incoming\Web3Payments;

use App\Models\Currency\Currency;
use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusState;
use App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers\CheckReceivedAmount;
use App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers\CheckTokenTransferReceivedAmount;
use App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers\CheckTokenTransferTransactionExist;
use App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers\CheckTokenTransferTransactionStatus;
use App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers\CheckTransactionExist;
use App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers\CheckTransactionNotExpired;
use App\Services\Merchant\Handlers\Incoming\Web3Payments\CheckStatusHandlers\CheckTransactionStatus;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusChain;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusContext;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;

class CheckStatusHandler
{
    public function handle(ExchangeRequest $exchangeRequest) : void
    {
        $handlers = $this->getHandlers($exchangeRequest);

        $checkStatusChain = new CheckStatusChain();
        foreach ($handlers as $handler) {
            $checkStatusChain->pushHandler($handler);
        }

        $checkStatusContext = new CheckStatusContext($handlers[0], $checkStatusChain);
        $checkStatusContext->handle(new CheckStatusRequest($exchangeRequest));
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return CheckStatusState[]
     */
    private function getHandlers(ExchangeRequest $exchangeRequest) : array
    {
        if (false === $this->isToken($exchangeRequest->givenCurrency)) {
            return [
                app(CheckTransactionExist::class),
                app(CheckReceivedAmount::class),
                app(CheckTransactionNotExpired::class),
                app(CheckTransactionStatus::class),
            ];
        }

        return [
            app(CheckTokenTransferTransactionExist::class),
            app(CheckTokenTransferReceivedAmount::class),
            app(CheckTransactionNotExpired::class),
            app(CheckTokenTransferTransactionStatus::class),
        ];
    }

    private function isToken(Currency $currency) : bool
    {
        return $currency->code !== 'ETH';
    }
}
