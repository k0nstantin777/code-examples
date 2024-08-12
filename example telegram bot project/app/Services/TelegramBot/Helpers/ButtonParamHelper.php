<?php

namespace App\Services\TelegramBot\Helpers;

class ButtonParamHelper
{
    public function encode(array $params) : string
    {
        return json_encode($params);
    }

    public function decode(string $encodeString) : array
    {
        return json_decode($encodeString, true) ?? [];
    }
}
