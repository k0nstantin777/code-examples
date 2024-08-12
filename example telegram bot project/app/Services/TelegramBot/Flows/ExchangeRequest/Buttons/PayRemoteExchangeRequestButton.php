<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Buttons;

use App\Services\TelegramBot\Buttons\WithoutParamsInlineButton;

class PayRemoteExchangeRequestButton extends WithoutParamsInlineButton
{
    protected function getPrefix() : string
    {
        return 'pay_remote_exchange_request_action';
    }
}