<?php

namespace App\Services\TelegramBot\Flows\ChangeLanguage\States;

use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use Exception;

class SelectedLanguageState extends ChatLanguageFlowState
{
    public function afterChangeHandle() : void
    {
    }

    /**
     * @throws InvalidBotActionException
     * @throws Exception
     */
    public function callbackQueryHandle() : void
    {
        throw new InvalidBotActionException();
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}
