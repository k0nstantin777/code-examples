<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectCalculateSumTypeButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\SelectedCalculateSumTypeHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowSelectCalculateSumTypeMenuHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SelectedExchangeDirectionState extends ExchangeRequestFlowState
{

    /**
     * @throws BindingResolutionException
     * @throws JsonRpcErrorResponseException
     * @throws TelegramSDKException
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function afterChangeHandle() : void
    {
        $handler = app()->make(ShowSelectCalculateSumTypeMenuHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidBotActionException
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function callbackQueryHandle() : void
    {
        $buttonService = app(TelegramBotButtonService::class);

        if (!$buttonService->isButtonPressed(app(SelectCalculateSumTypeButton::class))) {
            throw new InvalidBotActionException();
        }

        $handler = app()->make(SelectedCalculateSumTypeHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();

        $this->exchangeRequest->changeState(app(AwaitEnterAmountState::class));
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}