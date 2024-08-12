<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Buttons;

use App\Services\TelegramBot\Buttons\WithoutParamsInlineButton;

class SendExchangeRequestToServerButton extends WithoutParamsInlineButton
{
    protected function getPrefix() : string
    {
        return 'send_exchange_request_to_server';
    }
}