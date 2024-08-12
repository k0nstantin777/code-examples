<?php

namespace App\Services\TelegramBot\ValueObjects;

use App\Domains\User\Models\User;
use App\Services\Language\Enums\LanguageCode;
use App\Services\TelegramBot\Flows\ChangeLanguage\States\ChatLanguageFlowState;
use App\Services\TelegramBot\Flows\ChangeLanguage\States\NewState;

class ChatLanguage
{
    private ChatLanguageFlowState $state;
    private ?LanguageCode $lang = null;

    public function __construct(
        private readonly User $user,
    ) {
        $this->changeState(app(NewState::class));
    }

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user->refresh();
    }

    public function changeState(ChatLanguageFlowState $state) : void
    {
        $this->state = $state;
        $this->state->setChatLanguage($this);
        $this->state->afterChangeHandle();
    }

    public function getState() : ChatLanguageFlowState
    {
        return $this->state;
    }

    public function setLang(LanguageCode $lang) : void
    {
        $this->lang = $lang;
    }

    public function getLang() : ?LanguageCode
    {
        return $this->lang;
    }
}