<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\EnteredEmailHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowEnterEmailOrLoginHandler;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;

class AwaitEnterEmailOrLoginState extends ExchangeRequestFlowState
{
    /**
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function afterChangeHandle() : void
    {
        $handler = app()->make(ShowEnterEmailOrLoginHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();
    }

    /**
     * @throws InvalidBotActionException
     */
    public function callbackQueryHandle() : void
    {
        throw new InvalidBotActionException();
    }

    /**
     * @throws InvalidBotActionException|BindingResolutionException
     */
    public function messageHandle() : void
    {
        $handler = app()->make(EnteredEmailHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();

        $this->exchangeRequest->changeState(app(AwaitEnterFormAttributesState::class));
    }
}
