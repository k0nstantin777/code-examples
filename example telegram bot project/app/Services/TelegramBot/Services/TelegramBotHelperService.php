<?php

namespace App\Services\TelegramBot\Services;

use App\Domains\User\Models\User;
use App\Domains\User\Services\UserReadService;
use App\Services\TelegramBot\ValueObjects\Bot;
use Telegram\Bot\Objects\Update;

class TelegramBotHelperService
{
    public function __construct(
        protected UserReadService $userReadService,
    ) {
    }

    public function isCallbackQuery(Update $update): bool
    {
        return (bool) $update->callbackQuery;
    }

    public function isMessage(Update $update): bool
    {
        return (bool) $update->message;
    }

    public function getUser(Update $update, Bot $bot) : User
    {
        $chatId = $update->getChat()->id;

        return $this->userReadService->getByBotAndChat($bot->name, $chatId);
    }
}
