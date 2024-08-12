<?php

namespace App\Services\Homebase;

use App\Services\Homebase\Api\Services\EmployeeApiService;
use App\Services\Homebase\Api\Services\ShiftApiService;
use App\Services\Homebase\Api\Services\TimecardApiService;
use Illuminate\Support\ServiceProvider;

class HomebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = config('services.homebase');

        $this->app->bind(EmployeeApiService::class, function () use ($config) {
            return new EmployeeApiService($config);
        });
        $this->app->bind(TimecardApiService::class, function () use ($config) {
            return new TimecardApiService($config);
        });
        $this->app->bind(ShiftApiService::class, function () use ($config) {
            return new ShiftApiService($config);
        });
    }
}
