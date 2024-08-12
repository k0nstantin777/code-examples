<?php

namespace App\Services\TelegramBot\Flows\ChangeLanguage\States;

use App\Services\TelegramBot\Flows\State;
use App\Services\TelegramBot\ValueObjects\ChatLanguage;

abstract class ChatLanguageFlowState implements State
{
    protected ?ChatLanguage $chatLanguage = null;

    public function getChatLanguage() : ChatLanguage
    {
        return $this->chatLanguage;
    }

    public function setChatLanguage(ChatLanguage $chatLanguage) : void
    {
        $this->chatLanguage = $chatLanguage;
    }
}
