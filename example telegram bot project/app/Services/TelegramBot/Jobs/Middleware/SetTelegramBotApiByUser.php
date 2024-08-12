<?php

namespace App\Services\TelegramBot\Jobs\Middleware;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Exceptions\BotNotFoundException;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\Services\TelegramBotConfigService;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class SetTelegramBotApiByUser
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
     * @return void
     * @throws BotNotFoundException
     * @throws UnknownProperties
     */
    public function handle($job, $next) : void
    {
        $telegramBotConfigService = app(TelegramBotConfigService::class);
        $bot = $telegramBotConfigService->getBotByName($this->user->telegram_bot_name);
        app(TelegramBotApi::class)->setBot($bot);

        $next($job);
    }
}