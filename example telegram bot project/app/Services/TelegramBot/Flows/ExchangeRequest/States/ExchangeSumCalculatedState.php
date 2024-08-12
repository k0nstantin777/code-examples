<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowBotActionsMenuHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ExchangeSumCalculatedState extends ExchangeRequestFlowState
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
            $this->exchangeRequest->changeState(app(SelectedExchangeDirectionState::class));
            return;
        }

        if (!$buttonService->isNextBtnPressed()) {
            throw new InvalidBotActionException();
        }

        if ($this->exchangeRequest->getUser()->getExchangerUserIdOrNull()) {
            $this->exchangeRequest->changeState(app(AwaitEnterFormAttributesState::class));
        } else {
            $this->exchangeRequest->changeState(app(AwaitEnterEmailOrLoginState::class));
        }
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}
