<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectGivenCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\SelectedGivenCurrencyHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowSelectGivenCurrencyMenuHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class AwaitSelectGivenCurrencyState extends ExchangeRequestFlowState
{
    /**
     * @throws UnknownProperties
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws BindingResolutionException
     * @throws ValidationException
     */
    public function afterChangeHandle() : void
    {
        $handler = app()->make(ShowSelectGivenCurrencyMenuHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();
    }

    /**
     * @throws InvalidBotActionException
     * @throws UnknownProperties
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws BindingResolutionException
     * @throws ValidationException
     */
    public function callbackQueryHandle() : void
    {
        $buttonService = app(TelegramBotButtonService::class);

        if (!$buttonService->isButtonPressed(app(SelectGivenCurrencyButton::class))) {
            throw new InvalidBotActionException();
        }

        $handler = app()->make(SelectedGivenCurrencyHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();

        $this->exchangeRequest->changeState(app(SelectedGivenCurrencyState::class));
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}