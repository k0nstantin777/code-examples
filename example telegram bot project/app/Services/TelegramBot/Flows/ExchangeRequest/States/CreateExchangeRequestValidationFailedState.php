<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\SendRequestExchangeValidationFiledHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;

class CreateExchangeRequestValidationFailedState extends ExchangeRequestFlowState
{
    /**
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function afterChangeHandle() : void
    {
        $handler = app()->make(SendRequestExchangeValidationFiledHandler::class, [
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

        if (!$buttonService->isCreateNewExchangeRequestBtnPressed()) {
            throw new InvalidBotActionException();
        }

        $this->exchangeRequest->reset();
        $this->exchangeRequest->changeState(app(AwaitSelectGivenCurrencyState::class));
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}
