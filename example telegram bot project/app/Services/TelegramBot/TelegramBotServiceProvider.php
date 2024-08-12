<?php

namespace App\Services\TelegramBot;

use App\Http\Controllers\TelegramWebhookController;
use App\Services\Storages\CacheStorage;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\Services\TelegramBotConfigService;
use App\Services\TelegramBot\Services\TelegramBotRequestService;
use App\Services\TelegramBot\Storages\ActiveExchangeRequestStorage;
use App\Services\TelegramBot\Storages\CacheStorages\ActiveExchangeRequestCacheStorage;
use App\Services\TelegramBot\Storages\CacheStorages\ChatCacheStorage;
use App\Services\TelegramBot\Storages\CacheStorages\ChatLanguageCacheStorage;
use App\Services\TelegramBot\Storages\CacheStorages\ExchangeDirectionCacheStorage;
use App\Services\TelegramBot\Storages\CacheStorages\ExchangeRequestCacheStorage;
use App\Services\TelegramBot\Storages\ChatLanguageStorage;
use App\Services\TelegramBot\Storages\ChatStorage;
use App\Services\TelegramBot\Storages\ExchangeDirectionStorage;
use App\Services\TelegramBot\Storages\ExchangeRequestStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class TelegramBotServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->registerStorages();

        Request::macro('getBot', function () {
            return app()->make(TelegramBotRequestService::class)->getBotFromRequest();
        });
    }

    /**
     * @throws UnknownProperties
     */
    public function boot() : void
    {
        $this->bootBot();
    }

    /**
     * @throws UnknownProperties
     */
    protected function bootBot()
    {
        $bot = request()->getBot();

        $telegramBotConfigService =  app(TelegramBotConfigService::class);

        if (!$bot) {
            $bot = $telegramBotConfigService->getDefaultBot();
        }

        Route::post('/' . $bot->token . '/telegram-webhook', TelegramWebhookController::class)
            ->name('telegram-webhook');

        $this->app->singleton(TelegramBotApi::class, function () use ($bot) {
            return new TelegramBotApi($bot);
        });
    }

    protected function registerStorages() : void
    {
        $this->app->bind(ExchangeRequestStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('telegram_bot_exchange_requests_');
            $cacheStorage->setTtl(config('telegram.cached_time.exchange_request'));
            return new ExchangeRequestCacheStorage($cacheStorage);
        });

        $this->app->bind(ActiveExchangeRequestStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('telegram_bot_exchange_requests_');
            $cacheStorage->setTtl(config('telegram.cached_time.exchange_request'));
            return new ActiveExchangeRequestCacheStorage($cacheStorage);
        });

        $this->app->bind(ExchangeDirectionStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('telegram_bot_exchange_directions_');
            $cacheStorage->setTtl(config('telegram.cached_time.exchange_direction'));
            return new ExchangeDirectionCacheStorage($cacheStorage);
        });

        $this->app->bind(ChatStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('telegram_bot_chat_');
            $cacheStorage->setTtl(config('telegram.cached_time.flow'));
            return new ChatCacheStorage($cacheStorage);
        });

        $this->app->bind(ChatLanguageStorage::class, function ($app) {
            $cacheStorage = $app->make(CacheStorage::class);
            $cacheStorage->setPrefix('telegram_bot_chat_language_');
            $cacheStorage->setTtl(config('telegram.cached_time.flow'));
            return new ChatLanguageCacheStorage($cacheStorage);
        });
    }
}
