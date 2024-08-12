<?php

namespace App\Services\TelegramBot\Commands;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\HelpCommand as BaseHelpCommand;

class HelpCommand extends BaseHelpCommand
{
    protected $aliases = [];

    public function getDescription(): string
    {
        return __('Help command, Get a list of commands');
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function handle()
    {
        $messageSettingsService = app(MessageSettingsService::class);
        $text = $messageSettingsService->getFormattedByCode(MessageCode::WELCOME) . "\n";

        $commands = $this->telegram->getCommands();

        foreach ($commands as $name => $handler) {
            /* @var Command $handler */
            $text .= sprintf('/%s - %s' . PHP_EOL, $name, $handler->getDescription());
        }

        $this->replyWithMessage(compact('text'));
    }
}
