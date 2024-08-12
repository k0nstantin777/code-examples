<?php

namespace App\Services\TelegramBot\Jobs\Middleware;

use Illuminate\Contracts\Redis\LimiterTimeoutException;
use Illuminate\Support\Facades\Redis;

class ExchangerRateLimited
{
    /**
     * Process the queued job.
     *
     * @param mixed $job
     * @param callable $next
     * @return mixed
     * @throws LimiterTimeoutException
     */
    public function handle($job, $next) : void
    {
        Redis::throttle('exchanger-rate-limited')
            ->allow(5)->every(10)
            ->then(function () use ($job, $next) {
                // Lock obtained...

                $next($job);
            }, function () use ($job) {
                // Could not obtain lock...

                $job->release(5);
            });
    }
}