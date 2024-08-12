<?php

namespace Tests\Mocks\TelegramBot;

use Telegram\Bot\BotsManager;
use Telegram\Bot\Api;

class FakeBotsManager extends BotsManager
{
    protected array $responses = [];

    protected function makeBot($name): Api
    {
        $config = $this->getBotConfig($name);

        $token = data_get($config, 'token');

        $telegram = new TelegramBotApi(
            $token,
            $this->getConfig('async_requests', false),
            $this->getConfig('http_client_handler', null)
        );

        $telegram->setResponses($this->responses);

        // Check if DI needs to be enabled for Commands
        if ($this->getConfig('resolve_command_dependencies', false) && isset($this->container)) {
            $telegram->setContainer($this->container);
        }

        $commands = data_get($config, 'commands', []);
        $commands = $this->parseBotCommands($commands);

        // Register Commands
        try {
            $telegram->addCommands($commands);
        } catch (\Throwable $exception) {
            $exception->getMessage();
        }


        return $telegram;
    }

    public function setResponses(array $responses): void
    {
        $this->responses = $responses;
    }
}
