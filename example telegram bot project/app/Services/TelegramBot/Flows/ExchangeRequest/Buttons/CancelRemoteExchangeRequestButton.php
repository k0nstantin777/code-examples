<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Buttons;

use App\Services\TelegramBot\Buttons\BaseInlineButton;
use Telegram\Bot\Keyboard\Button;

class CancelRemoteExchangeRequestButton extends BaseInlineButton
{
    public const ID_PARAM = 'id';

    protected function getPrefix() : string
    {
        return 'c_r_e_r_a'; // cancel_remote_exchange_request_action, shorted for accept telegram string length.
    }

    protected function validateParams(array $params) : bool
    {
        return isset($params[self::ID_PARAM]);
    }

    public function makeWithId(string $name, string $id) : Button
    {
        return $this->make($name, [
            self::ID_PARAM => $id,
        ]);
    }

    public function getExchangeRequestId(string $hash)
    {
        $params = $this->parseParams($hash);

        if (!isset($params[self::ID_PARAM])) {
            throw new \InvalidArgumentException('Invalid exchange request');
        }

        return $params[self::ID_PARAM];
    }
}