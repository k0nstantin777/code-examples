<?php

namespace App\Services\Merchant\Handlers\Incoming\WhiteBit\CryptoCurrency;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusChain;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusContext;
use App\Services\Merchant\Handlers\Incoming\WhiteBit\CheckStatusHandlers\CheckReceivedAmount;
use App\Services\Merchant\Handlers\Incoming\WhiteBit\CryptoCurrency\CheckStatusHandlers\CheckTransactionExist;
use App\Services\Merchant\Handlers\Incoming\WhiteBit\CheckStatusHandlers\CheckTransactionNotExpired;
use App\Services\Merchant\Handlers\Incoming\WhiteBit\CheckStatusHandlers\CheckTransactionStatus;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;

class CheckStatusHandler
{
    public function handle(ExchangeRequest $exchangeRequest) : void
    {
        $handlers = [
            app(CheckTransactionExist::class),
            app(CheckReceivedAmount::class),
            app(CheckTransactionNotExpired::class),
            app(CheckTransactionStatus::class),
        ];

        $checkStatusChain = new CheckStatusChain();
        foreach ($handlers as $handler) {
            $checkStatusChain->pushHandler($handler);
        }

        $checkStatusContext = new CheckStatusContext($handlers[0], $checkStatusChain);
        $checkStatusContext->handle(new CheckStatusRequest($exchangeRequest));
    }
}
