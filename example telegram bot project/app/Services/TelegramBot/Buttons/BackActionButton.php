<?php

namespace App\Services\TelegramBot\Buttons;

class BackActionButton extends WithoutParamsInlineButton
{
    protected function getPrefix() : string
    {
        return 'back_bot_action';
    }
}