<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowCurrentExchangeRequestHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowResetOrCreateExchangeRequestMenuHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class AwaitConfirmExchangeRequestState extends ExchangeRequestFlowState
{
    /**
     * @throws BindingResolutionException
     * @throws InvalidBotActionException
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function afterChangeHandle() : void
    {
        $handler = app()->make(ShowCurrentExchangeRequestHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);

        $handler->setNext(app()->make(ShowResetOrCreateExchangeRequestMenuHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]));
        $handler->handle();
    }

    /**
     * @throws InvalidBotActionException
     * @throws Exception
     */
    public function callbackQueryHandle() : void
    {
        $buttonService = app(TelegramBotButtonService::class);

        if ($buttonService->isCreateNewExchangeRequestBtnPressed()) {
            $this->exchangeRequest->reset();
            $this->exchangeRequest->changeState(app(AwaitSelectGivenCurrencyState::class));
            return;
        }

        if (!$buttonService->isSendExchangeRequestBtnPressed()) {
            throw new InvalidBotActionException();
        }

        if (!$this->exchangeRequest->hasAuthData()) {
            $this->exchangeRequest->changeState(app(AwaitEnterEmailOrLoginState::class));
            return;
        }

        $this->exchangeRequest->changeState(app(ConfirmedExchangeRequestState::class));
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}
