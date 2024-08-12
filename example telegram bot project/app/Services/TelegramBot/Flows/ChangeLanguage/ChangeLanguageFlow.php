<?php

namespace App\Services\TelegramBot\Flows\ChangeLanguage;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Flows\ChangeLanguage\States\AwaitSelectLanguageState;
use App\Services\TelegramBot\Flows\Flow;
use App\Services\TelegramBot\Services\TelegramBotChatLanguageService;
use App\Services\TelegramBot\Services\TelegramBotHelperService;
use App\Services\TelegramBot\Services\TelegramBotStateService;
use App\Services\TelegramBot\ValueObjects\ChatLanguage;
use Telegram\Bot\Objects\Update;

class ChangeLanguageFlow extends Flow
{
    public function __construct(
        protected TelegramBotStateService $telegramBotStateService,
        protected TelegramBotHelperService $telegramBotHelperService,
        protected TelegramBotChatLanguageService $telegramBotChatLanguageService,
    ) {
    }

    public function handleRequest(Update $update) : void
    {
        $user = $this->telegramBotHelperService->getUser($update, request()->getBot());
        $chatLanguage = $this->telegramBotChatLanguageService->getByUserId($user->id);

        if (!$chatLanguage) {
            $this->createNew($user);
            return;
        }

        $state = $chatLanguage->getState();

        $this->telegramBotStateService->stateProcessing($state, $update);

        $this->telegramBotChatLanguageService->save($state->getChatLanguage());
    }


    public function createNew(User $user)
    {
        $chatLanguage = new ChatLanguage($user);
        $chatLanguage->changeState(app(AwaitSelectLanguageState::class));

        $this->telegramBotChatLanguageService->save($chatLanguage);
    }
}
