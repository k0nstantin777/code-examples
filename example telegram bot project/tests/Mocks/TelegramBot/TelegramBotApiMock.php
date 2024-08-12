<?php

namespace Tests\Mocks\TelegramBot;

use App\Services\TelegramBot\Services\TelegramBotConfigService;
use App\Services\TelegramBot\Services\TelegramBotRequestService;
use Mockery\MockInterface;
use Telegram\Bot\BotsManager;

trait TelegramBotApiMock
{
    public function initTelegramBotApiMock(array $responses = []): void
    {
        $botsManager = new FakeBotsManager(config('telegram'));
        $botsManager->setResponses($responses);

        $this->instance(
            BotsManager::class,
            $botsManager
        );

        $this->partialMock(TelegramBotRequestService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getBotFromRequest')
                ->andReturn(app(TelegramBotConfigService::class)->getDefaultBot());
        });
    }
}
