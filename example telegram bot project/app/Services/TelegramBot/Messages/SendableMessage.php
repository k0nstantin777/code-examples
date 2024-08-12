<?php

namespace App\Services\TelegramBot\Messages;

interface SendableMessage
{
    public function __invoke(...$params) : array;
}
