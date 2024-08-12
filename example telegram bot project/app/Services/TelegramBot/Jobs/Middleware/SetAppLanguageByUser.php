<?php

namespace App\Services\TelegramBot\Jobs\Middleware;

use App\Domains\User\Models\User;
use App\Services\Language\Enums\LanguageCode;
use App\Services\Language\LanguageService;

class SetAppLanguageByUser
{
    public function __construct(
        private readonly User $user
    ) {
    }

    /**
     * Process the queued job.
     *
     * @param mixed $job
     * @param callable $next
     * @return mixed
     */
    public function handle($job, $next) : void
    {
        app(LanguageService::class)->setAppLanguage(LanguageCode::from($this->user->lang));

        $next($job);
    }
}