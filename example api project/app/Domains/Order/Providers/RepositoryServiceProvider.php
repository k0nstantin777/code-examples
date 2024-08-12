<?php

namespace App\Domains\Order\Providers;

use App\Domains\Order\Repositories\Contracts\PreparedOrderRepository;
use App\Domains\Order\Repositories\Eloquent\PreparedOrderEloquentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function boot() : void
    {
		$this->app->bind(PreparedOrderRepository::class, PreparedOrderEloquentRepository::class);
    }
}
