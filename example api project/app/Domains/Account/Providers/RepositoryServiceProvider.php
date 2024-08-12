<?php

namespace App\Domains\Account\Providers;

use App\Domains\Account\Repositories\Contracts\ApiUserRepository;
use App\Domains\Account\Repositories\Eloquent\ApiUserEloquentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
		$this->app->bind(ApiUserRepository::class, ApiUserEloquentRepository::class);
    }
}
