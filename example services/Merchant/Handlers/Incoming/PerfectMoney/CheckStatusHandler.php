<?php

namespace App\Services\Merchant\Handlers\Incoming\PerfectMoney;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusChain;
use App\Services\Merchant\Handlers\Incoming\PerfectMoney\CheckStatusHandlers\CheckAddressExist;
use App\Services\Merchant\Handlers\Incoming\PerfectMoney\CheckStatusHandlers\CheckReceivedAmount;
use App\Services\Merchant\Handlers\Incoming\PerfectMoney\CheckStatusHandlers\CheckTransactionExist;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusContext;
use App\Services\Merchant\Handlers\Incoming\PerfectMoney\CheckStatusHandlers\CheckTransactionStatus;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;

class CheckStatusHandler
{
    public function handle(ExchangeRequest $exchangeRequest) : void
    {
        $handlers = [
            app(CheckTransactionExist::class),
            app(CheckAddressExist::class),
            app(CheckReceivedAmount::class),
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
