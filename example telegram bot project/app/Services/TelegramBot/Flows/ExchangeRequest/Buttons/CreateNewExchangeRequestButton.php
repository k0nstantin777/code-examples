<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Buttons;

use App\Services\TelegramBot\Buttons\WithoutParamsInlineButton;

class CreateNewExchangeRequestButton extends WithoutParamsInlineButton
{
    protected function getPrefix() : string
    {
        return 'create_new_exchange_request_action';
    }
}