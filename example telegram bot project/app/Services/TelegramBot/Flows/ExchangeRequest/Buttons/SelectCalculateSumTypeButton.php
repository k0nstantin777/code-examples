<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Buttons;

use App\Services\TelegramBot\Buttons\BaseInlineButton;
use App\Services\TelegramBot\Enums\CalculateSumType;
use Telegram\Bot\Keyboard\Button;

class SelectCalculateSumTypeButton extends BaseInlineButton
{
    public const TYPE_PARAM = 't';

    protected function getPrefix() : string
    {
        return 'calculate_sum_type';
    }

    protected function validateParams(array $params) : bool
    {
        if (count($params) !== 1 || !isset($params[self::TYPE_PARAM])) {
            return false;
        }

        foreach ($params as $value) {
            if ($value === CalculateSumType::GIVEN_CURRENCY->value ||
                $value === CalculateSumType::RECEIVED_CURRENCY->value
            ) {
                return true;
            }
        }

        return false;
    }

    public function makeForGivenCurrency(string $code) : Button
    {
        return $this->make(__('To enter') . ' ' . $code, [
            self::TYPE_PARAM => CalculateSumType::GIVEN_CURRENCY->value,
        ]);
    }

    public function makeForReceivedCurrency(string $code) : Button
    {
        return $this->make(__('To enter') . ' ' . $code, [
            self::TYPE_PARAM => CalculateSumType::RECEIVED_CURRENCY->value,
        ]);
    }

    public function getCalculateSumType(string $hash)
    {
        $params = $this->parseParams($hash);

        return $params[self::TYPE_PARAM];
    }
}
