<?php

namespace App\Services\TelegramBot\Services;

use App\Services\TelegramBot\Exceptions\BotNotFoundException;
use App\Services\TelegramBot\ValueObjects\Bot;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\BotsManager;

class TelegramBotConfigService
{
    /**
     * @return array|Bot[]
     * @throws UnknownProperties
     */
    public function getBots() : array
    {
        $bots = [];
        foreach (config('telegram.bots') as $name => $botConfig) {
            $bots[] = new Bot(
                name: $name,
                username: $name,
                token: $botConfig['token'],
                webhook_url: $this->getWebhookUrl($botConfig['token']),
            );
        }

        return $bots;
    }

    /**
     * @throws UnknownProperties|BotNotFoundException
     */
    public function getBotByToken(string $token) : Bot
    {
        foreach ($this->getBots() as $bot) {
            if ($bot->token === $token) {
                return $bot;
            }
        }

        throw new BotNotFoundException('Bot not found with token: ' . $token);
    }

    /**
     * @throws UnknownProperties|BotNotFoundException
     */
    public function getBotByName(string $name) : Bot
    {
        foreach ($this->getBots() as $bot) {
            if ($bot->name === $name) {
                return $bot;
            }
        }

        throw new BotNotFoundException('Bot not found with name: ' . $name);
    }

    public function setAsDefaultBot(Bot $bot) : void
    {
        config(['telegram.default' => $bot->name]);
        app(BotsManager::class)->setDefaultBot($bot->name);
    }

    /**
     * @throws UnknownProperties
     */
    public function getDefaultBot() : Bot
    {
        return current($this->getBots());
    }

    public function getWebhookUrl(string $token) : string
    {
        $path = str_replace(':token', $token, $this->getWebhookPathFormat());

        return config('telegram.webhook_uri') . $path;
    }

    public function getWebhookPathFormat() : string
    {
        return config('telegram.webhook_path_format');
    }
}
