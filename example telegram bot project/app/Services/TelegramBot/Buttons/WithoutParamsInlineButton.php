<?php

namespace App\Services\TelegramBot\Buttons;

use App\Services\TelegramBot\Helpers\ButtonParamHelper;
use Telegram\Bot\Keyboard\Button;
use Telegram\Bot\Keyboard\Keyboard;

abstract class WithoutParamsInlineButton extends BaseInlineButton
{
    protected function validateParams(array $params) : bool
    {
        return true;
    }
}
