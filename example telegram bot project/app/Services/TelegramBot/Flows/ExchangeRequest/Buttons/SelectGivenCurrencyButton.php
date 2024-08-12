<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Buttons;

use App\Services\TelegramBot\Buttons\BaseInlineButton;

class SelectGivenCurrencyButton extends BaseInlineButton
{
    public const CURRENCY_ID_PARAM = 'id';

    protected function getPrefix() : string
    {
        return 'given_currency_id';
    }

    protected function validateParams(array $params) : bool
    {
        return isset($params[self::CURRENCY_ID_PARAM]);
    }

    public function getGivenCurrencyId(string $hash)
    {
        $params = $this->parseParams($hash);

        if (!isset($params[self::CURRENCY_ID_PARAM])) {
            throw new \InvalidArgumentException('Invalid given currency selected');
        }

        return $params[self::CURRENCY_ID_PARAM];
    }
}