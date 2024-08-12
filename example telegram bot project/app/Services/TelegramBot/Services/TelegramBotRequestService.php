<?php

namespace App\Services\TelegramBot\Services;

use App\Services\TelegramBot\Exceptions\BotNotFoundException;
use App\Services\TelegramBot\ValueObjects\Bot;
use Illuminate\Http\Request;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class TelegramBotRequestService
{
    public function __construct(
        private readonly Request $request,
        private readonly TelegramBotConfigService $telegramBotConfigService,
    ) {
    }

    /**
     * @throws UnknownProperties
     */
    public function getBotFromRequest() : ?Bot
    {
        try {
            $requestUri = str_replace('/', '', $this->request->getPathInfo());
            $webhookPath = str_replace('/', '', $this->telegramBotConfigService->getWebhookPathFormat());
            $replacers = explode(':token', $webhookPath);
            $botToken = str_replace($replacers, '', $requestUri);

            return $this->telegramBotConfigService->getBotByToken($botToken);
        } catch (BotNotFoundException) {
            return null;
        }
    }
}
