<?php

namespace App\Services\Merchant\Storages;

use App\Services\Storages\CacheStorage;
use App\Settings\ExchangeProcessSettings;

abstract class BaseMerchantEntitiesStorage
{
    public function __construct(
        protected CacheStorage $storage,
        ExchangeProcessSettings $exchangeProcessSettings
    ) {
        $this->storage->setTtl(
            config('exchange-processing.time_check_payment_seconds') +
            $exchangeProcessSettings->time_to_pay_exchange_request_in_minutes * 60
        );
    }

    public function forget(string $key): void
    {
        $this->storage->forget($key);
    }
}
