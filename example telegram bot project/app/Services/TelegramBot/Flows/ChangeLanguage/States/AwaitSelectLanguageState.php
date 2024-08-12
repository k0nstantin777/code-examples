<?php

namespace App\Services\TelegramBot\Flows\ChangeLanguage\States;

use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ChangeLanguage\Buttons\SelectLanguageButton;
use App\Services\TelegramBot\Flows\ChangeLanguage\Handlers\ShowChangeLangMenuHandler;
use App\Services\TelegramBot\Flows\ChangeLanguage\Handlers\SelectedLanguageHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Exception;
use Telegram\Bot\Exceptions\TelegramSDKException;

class AwaitSelectLanguageState extends ChatLanguageFlowState
{
    /**
     * @throws TelegramSDKException
     */
    public function afterChangeHandle() : void
    {
        $handler = app(ShowChangeLangMenuHandler::class);
        $handler->handle();
    }

    /**
     * @throws InvalidBotActionException
     * @throws Exception
     */
    public function callbackQueryHandle() : void
    {
        $buttonService = app(TelegramBotButtonService::class);

        if (!$buttonService->isButtonPressed(app(SelectLanguageButton::class))) {
            throw new InvalidBotActionException();
        }

        $handler = app(SelectedLanguageHandler::class, ['chatLanguage' => $this->chatLanguage]);
        $handler->handle();

        $this->chatLanguage->changeState(app(SelectedLanguageState::class));
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}
