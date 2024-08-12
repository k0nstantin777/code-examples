<?php

namespace App\Services\Merchant\Storages;

use App\Services\Merchant\ValueObjects\TransactionInterface;
use App\Services\Storages\CacheStorage;
use App\Settings\ExchangeProcessSettings;

class TransactionsStorage extends BaseMerchantEntitiesStorage
{
    public function __construct(CacheStorage $storage, ExchangeProcessSettings $exchangeProcessSettings)
    {
        parent::__construct($storage, $exchangeProcessSettings);

        $this->storage->setPrefix('transactions_storage');
    }

    public function get(string $key) : ?TransactionInterface
    {
        return $this->storage->get($key);
    }

    public function save(string $key, TransactionInterface $transaction): void
    {
        $this->storage->save($key, $transaction);
    }
}
