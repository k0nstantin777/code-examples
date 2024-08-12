<?php

namespace App\Services\Exchanger;

use App\Services\Exchanger\Endpoints\GetExchangeDirection;
use App\Services\Exchanger\Endpoints\GetTelegramBot;
use App\Services\Exchanger\Endpoints\ListExchangeDirections;
use App\Services\Exchanger\Endpoints\ListSettings;
use App\Services\Exchanger\Endpoints\ListTelegramBots;
use App\Services\Exchanger\Services\ExchangeDirection\ExchangeDirectionCachedService;
use App\Services\Exchanger\Services\ExchangeDirection\ExchangeDirectionService;
use App\Services\Exchanger\Storages\ExchangeDirection\ExchangeDirectionCacheStorage;
use App\Services\Exchanger\Storages\ExchangeDirection\ExchangeDirectionCollectionCacheStorage;
use App\Services\Exchanger\Storages\ExchangeDirection\ExchangeDirectionStorage;
use App\Services\Exchanger\Storages\Settings\SettingsCollectionCacheStorage;
use App\Services\Exchanger\Storages\Settings\SettingsCollectionStorage;
use App\Services\Exchanger\Storages\TelegramBot\TelegramBotCacheStorage;
use App\Services\Exchanger\Storages\TelegramBot\TelegramBotListCacheStorage;
use App\Services\Exchanger\Storages\TelegramBot\TelegramBotListStorage;
use App\Services\Exchanger\Storages\TelegramBot\TelegramBotStorage;
use App\Services\Storages\CacheStorage;
use Illuminate\Support\ServiceProvider;

class ExchangerServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->app->bind(ExchangeDirectionCollectionCacheStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('exchange_direction_collection_');
            $cacheStorage->setTtl(config('services.exchanger.ttl_cache_in_seconds'));
            return new ExchangeDirectionCollectionCacheStorage($cacheStorage);
        });

        $this->app->bind(ExchangeDirectionStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('exchange_direction_');
            $cacheStorage->setTtl(config('services.exchanger.ttl_cache_in_seconds'));
            return new ExchangeDirectionCacheStorage($cacheStorage);
        });

        $this->app->bind(ExchangeDirectionService::class, function ($app) {
            return new ExchangeDirectionCachedService(
                $app->make(ListExchangeDirections::class),
                $app->make(GetExchangeDirection::class),
                $app->make(ExchangeDirectionCollectionCacheStorage::class),
                $app->make(ExchangeDirectionStorage::class),
            );
        });

        $this->app->bind(SettingsCollectionStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('settings_collection_');
            $cacheStorage->setTtl(config('services.exchanger.ttl_cache_in_seconds'));
            return new SettingsCollectionCacheStorage($cacheStorage);
        });

        $this->app->bind(ListSettings::class, function ($app) {
            return $app->make(\App\Services\Exchanger\Endpoints\Cached\ListSettings::class);
        });

        $this->app->bind(TelegramBotListStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('telegram_bots_lists_');
            $cacheStorage->setTtl(config('services.exchanger.ttl_cache_in_seconds'));
            return new TelegramBotListCacheStorage($cacheStorage);
        });

        $this->app->bind(ListTelegramBots::class, function ($app) {
            return $app->make(\App\Services\Exchanger\Endpoints\Cached\ListTelegramBots::class);
        });

        $this->app->bind(TelegramBotStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('telegram_bots_');
            $cacheStorage->setTtl(config('services.exchanger.ttl_cache_in_seconds'));
            return new TelegramBotCacheStorage($cacheStorage);
        });

        $this->app->bind(GetTelegramBot::class, function ($app) {
            return $app->make(\App\Services\Exchanger\Endpoints\Cached\GetTelegramBot::class);
        });
    }
}
