<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\EnteredExchangeAmountHandler;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class AwaitEnterAmountState extends ExchangeRequestFlowState
{

    public function afterChangeHandle() : void
    {
    }

    /**
     * @throws InvalidBotActionException
     */
    public function callbackQueryHandle() : void
    {
        throw new InvalidBotActionException();
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws BindingResolutionException
     * @throws ValidationException
     * @throws UnknownProperties
     * @throws TelegramSDKException
     */
    public function messageHandle() : void
    {
        $handler = app()->make(EnteredExchangeAmountHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->handle();

        $this->exchangeRequest->changeState(app(ExchangeSumCalculatedState::class));
    }
}
