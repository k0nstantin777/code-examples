<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Buttons;

use App\Services\TelegramBot\Buttons\BaseInlineButton;

class SelectReceivedCurrencyButton extends BaseInlineButton
{
    public const CURRENCY_ID_PARAM = 'id';
    public const EXCHANGE_DIRECTION_ID_PARAM = 'e_id';

    protected function getPrefix() : string
    {
        return 'received_currency_id';
    }

    protected function validateParams(array $params) : bool
    {
        return isset($params[self::EXCHANGE_DIRECTION_ID_PARAM], $params[self::CURRENCY_ID_PARAM]);
    }

    public function getReceivedCurrencyId(string $hash)
    {
        $params = $this->parseParams($hash);

        if (!isset($params[self::CURRENCY_ID_PARAM])) {
            throw new \InvalidArgumentException('Invalid received currency selected');
        }

        return $params[self::CURRENCY_ID_PARAM];
    }

    public function getExchangeDirectionId(string $hash)
    {
        $params = $this->parseParams($hash);

        if (!isset($params[self::EXCHANGE_DIRECTION_ID_PARAM])) {
            throw new \InvalidArgumentException('Invalid received currency selected');
        }

        return $params[self::EXCHANGE_DIRECTION_ID_PARAM];
    }
}