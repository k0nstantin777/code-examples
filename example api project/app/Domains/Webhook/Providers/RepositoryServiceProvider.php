<?php

namespace App\Domains\Webhook\Providers;

use App\Domains\Webhook\Repositories\Contracts\WebhookEventRepository;
use App\Domains\Webhook\Repositories\Contracts\WebhookRepository;
use App\Domains\Webhook\Repositories\Eloquent\WebhookEloquentRepository;
use App\Domains\Webhook\Repositories\Eloquent\WebhookEventEloquentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot() : void
    {
		$this->app->bind(WebhookRepository::class, WebhookEloquentRepository::class);
		$this->app->bind(WebhookEventRepository::class, WebhookEventEloquentRepository::class);
    }
}
