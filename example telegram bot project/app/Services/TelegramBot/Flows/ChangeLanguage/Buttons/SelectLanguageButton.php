<?php

namespace App\Services\TelegramBot\Flows\ChangeLanguage\Buttons;

use App\Services\TelegramBot\Buttons\BaseInlineButton;
use Telegram\Bot\Keyboard\Button;

class SelectLanguageButton extends BaseInlineButton
{
    public const LANG_PARAM = 'l';

    protected function getPrefix() : string
    {
        return 'bot_language';
    }

    protected function validateParams(array $params) : bool
    {
        return isset($params[self::LANG_PARAM]);
    }

    public function makeWithCode(string $name, string $code) : Button
    {
        return $this->make($name, [
            self::LANG_PARAM => $code
        ]);
    }

    public function getLang(string $hash)
    {
        $params = $this->parseParams($hash);

        if (!isset($params[self::LANG_PARAM])) {
            throw new \InvalidArgumentException('Invalid language selected');
        }

        return $params[self::LANG_PARAM];
    }
}