<?php

namespace App\Services\TelegramBot\Services;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Storages\ChatLanguageStorage;
use App\Services\TelegramBot\ValueObjects\ChatLanguage;

class TelegramBotChatLanguageService
{
    public function __construct(
        private readonly ChatLanguageStorage $chatLanguageStorage,
    ) {
    }

    public function getByUserId(int $userId) : ?ChatLanguage
    {
        return $this->chatLanguageStorage->get($userId);
    }

    public function save(ChatLanguage $chatLanguage) : void
    {
        $this->chatLanguageStorage->save($chatLanguage->getUser()->id, $chatLanguage);
    }

    public function delete(ChatLanguage $chatLanguage) : void
    {
        $this->chatLanguageStorage->remove($chatLanguage->getUser()->id);
    }

    public function resetForUser(User $user) : void
    {
        $chatLanguage = $this->getByUserId($user->id);

        if ($chatLanguage) {
            $this->delete($chatLanguage);
        }
    }
}
