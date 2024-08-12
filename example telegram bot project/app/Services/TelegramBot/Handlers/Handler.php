<?php

namespace App\Services\TelegramBot\Handlers;

interface Handler
{
    public function setNext(Handler $handler): Handler;

    public function handle(): void;
}
