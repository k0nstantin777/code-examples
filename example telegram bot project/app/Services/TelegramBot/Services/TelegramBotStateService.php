<?php

namespace App\Services\TelegramBot\Services;

use App\Services\TelegramBot\Flows\State;
use Telegram\Bot\Objects\Update;

class TelegramBotStateService
{
    public function __construct(
        private readonly TelegramBotHelperService $telegramBotHelperService,
    ) {
    }

    public function stateProcessing(State $state, Update $update) : void
    {
        if ($this->telegramBotHelperService->isCallbackQuery($update)) {
            $state->callbackQueryHandle();
        } elseif ($this->telegramBotHelperService->isMessage($update)) {
            $state->messageHandle();
        }
    }
}
