<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectReceivedCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\SelectedReceivedCurrencyHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowSelectReceivedCurrencyMenuHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SelectedGivenCurrencyState extends ExchangeRequestFlowState
{
    /**
     * @throws JsonRpcErrorResponseException
     * @throws TelegramSDKException
     * @throws UnknownProperties
     * @throws ValidationException
     * @throws BindingResolutionException
     */
    public function afterChangeHandle() : void
    {
        $handler = app()->make(ShowSelectReceivedCurrencyMenuHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);

        $handler->handle();
    }

    /**
     * @throws InvalidBotActionException
     * @throws UnknownProperties
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function callbackQueryHandle() : void
    {
        $buttonService = app(TelegramBotButtonService::class);

        if (!$buttonService->isButtonPressed(app(SelectReceivedCurrencyButton::class))) {
            throw new InvalidBotActionException();
        }

        $handler = app()->make(SelectedReceivedCurrencyHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();

        $this->exchangeRequest->changeState(app(SelectedReceivedCurrencyState::class));
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}