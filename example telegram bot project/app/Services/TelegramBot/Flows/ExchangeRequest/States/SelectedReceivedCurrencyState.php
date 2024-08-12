<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowBotActionsMenuHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SelectedReceivedCurrencyState extends ExchangeRequestFlowState
{
    /**
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function afterChangeHandle() : void
    {
        $handler = app()->make(ShowBotActionsMenuHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();
    }

    /**
     * @throws InvalidBotActionException
     */
    public function callbackQueryHandle() : void
    {
        $buttonService = app(TelegramBotButtonService::class);

        if ($buttonService->isBackBtnPressed()) {
            $this->exchangeRequest->changeState(app(SelectedGivenCurrencyState::class));
            return;
        } elseif ($buttonService->isNextBtnPressed()) {
            $this->exchangeRequest->changeState(app(SelectedExchangeDirectionState::class));
            return;
        }

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
