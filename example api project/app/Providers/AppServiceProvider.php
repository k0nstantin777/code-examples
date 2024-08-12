<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
		Response::macro('apiSuccess', function ($value) {
			return Response::json(['result' => $value]);
		});

		Response::macro('apiError', function ($value) {
			return Response::json(['error' => $value]);
		});
    }
}
