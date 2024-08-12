<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\CompleteExchangeRequestHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class CompleteExchangeRequestState extends ExchangeRequestFlowState
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
        $handler = app()->make(CompleteExchangeRequestHandler::class, [
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
