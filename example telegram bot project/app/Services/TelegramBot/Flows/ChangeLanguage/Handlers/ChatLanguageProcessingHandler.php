<?php

namespace App\Services\TelegramBot\Flows\ChangeLanguage\Handlers;

use App\Services\TelegramBot\Handlers\AbstractHandler;
use App\Services\TelegramBot\ValueObjects\ChatLanguage;
use App\Services\TelegramBot\Services\TelegramBotApi;

abstract class ChatLanguageProcessingHandler extends AbstractHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        protected ChatLanguage $chatLanguage,
    ) {
        parent::__construct($telegram);
    }
}
