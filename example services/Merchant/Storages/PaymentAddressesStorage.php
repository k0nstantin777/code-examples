<?php

namespace App\Services\Merchant\Storages;

use App\Services\Merchant\ValueObjects\PaymentAddressInterface;
use App\Services\Storages\CacheStorage;
use App\Settings\ExchangeProcessSettings;

class PaymentAddressesStorage extends BaseMerchantEntitiesStorage
{
    public function __construct(CacheStorage $storage, ExchangeProcessSettings $exchangeProcessSettings)
    {
        parent::__construct($storage, $exchangeProcessSettings);

        $this->storage->setPrefix('payment_address');
    }

    public function get(string $key) : ?PaymentAddressInterface
    {
        return $this->storage->get($key);
    }

    public function save(string $key, PaymentAddressInterface $paymentAddress): void
    {
        $this->storage->save($key, $paymentAddress);
    }
}
