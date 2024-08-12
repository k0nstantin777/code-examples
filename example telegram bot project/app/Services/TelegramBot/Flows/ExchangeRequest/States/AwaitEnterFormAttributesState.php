<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\EnteredFormAttributeValueHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowEnterFormAttributesHandler;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class AwaitEnterFormAttributesState extends ExchangeRequestFlowState
{
    /**
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function afterChangeHandle() : void
    {
        $telegramBotExchangeRequest = app(TelegramBotExchangeRequestService::class);
        $nextRequiredAttribute = $telegramBotExchangeRequest->getNextRequiredFormAttribute($this->exchangeRequest);

        if (null === $nextRequiredAttribute) {
            $this->exchangeRequest->changeState(app(AwaitConfirmExchangeRequestState::class));
            return;
        }

        $handler = app()->make(ShowEnterFormAttributesHandler::class, [
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
     * @throws BindingResolutionException
     * @throws JsonRpcErrorResponseException
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function messageHandle() : void
    {
        $handler = app()->make(EnteredFormAttributeValueHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();

        $telegramBotExchangeRequest = app(TelegramBotExchangeRequestService::class);
        $nextRequiredAttribute = $telegramBotExchangeRequest->getNextRequiredFormAttribute($this->exchangeRequest);

        if (null === $nextRequiredAttribute) {
            $this->exchangeRequest->changeState(app(AwaitConfirmExchangeRequestState::class));
            return;
        }

        $this->exchangeRequest->changeState(app(AwaitEnterFormAttributesState::class));
    }
}
