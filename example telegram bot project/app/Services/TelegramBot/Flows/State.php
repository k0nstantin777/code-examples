<?php

namespace App\Services\TelegramBot\Flows;

interface State
{
    public function afterChangeHandle() : void;
    public function callbackQueryHandle() : void;
    public function messageHandle() : void;
}
