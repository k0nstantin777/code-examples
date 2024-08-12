<?php

namespace App\Services\TelegramBot\Buttons;

class NextActionButton extends WithoutParamsInlineButton
{
    protected function getPrefix() : string
    {
        return 'next_bot_action';
    }
}