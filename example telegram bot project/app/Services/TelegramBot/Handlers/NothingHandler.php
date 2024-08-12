<?php

namespace App\Services\TelegramBot\Handlers;

class NothingHandler extends AbstractHandler
{
    public function handle(): void
    {
        // do nothing, wait correct action from chat
    }
}
