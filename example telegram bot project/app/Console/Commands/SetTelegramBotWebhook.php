<?php

namespace App\Console\Commands;

use App\Services\TelegramBot\Exceptions\BotNotFoundException;
use App\Services\TelegramBot\Services\TelegramBotConfigService;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Artisan\WebhookCommand;

class SetTelegramBotWebhook extends WebhookCommand
{
    /**
     * Setup Webhook.
     * @throws TelegramSDKException
     * @throws BotNotFoundException
     * @throws UnknownProperties
     */
    protected function setupWebhook() : void
    {
        $telegramBotsConfigService = app(TelegramBotConfigService::class);
        $token = data_get($this->config, 'token');
        $bot = $telegramBotsConfigService->getBotByToken($token);

        $params = ['url' => $bot->webhookUrl];
        $certificatePath = data_get($this->config, 'certificate_path', false);

        if ($certificatePath) {
            $params['certificate'] = $certificatePath;
        }

        $webhookConfig = data_get($this->config, 'webhook_params', []);

        $response = $this->telegram->setWebhook(array_merge($params, $webhookConfig));
        if ($response) {
            $this->info('Success: Your webhook has been set!');

            return;
        }

        $this->error('Your webhook could not be set!');
    }
}
