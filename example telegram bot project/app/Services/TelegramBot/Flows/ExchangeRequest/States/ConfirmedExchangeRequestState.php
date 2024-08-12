<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\SendExchangeRequestHandler;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ConfirmedExchangeRequestState extends ExchangeRequestFlowState
{

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function afterChangeHandle() : void
    {
        try {
            if (!$this->exchangeRequest->getRemoteId()) {
                $handler = app()->make(SendExchangeRequestHandler::class, [
                    'exchangeRequest' => $this->exchangeRequest,
                ]);
                $handler->handle();
            }

            $this->exchangeRequest->changeState(app(AwaitCustomerActionForExchangeRequestState::class));
        } catch (ValidationException $exception) {
            $this->exchangeRequest->setCreationValidationErrors($exception->errors());
            $this->exchangeRequest->changeState(app(CreateExchangeRequestValidationFailedState::class));
        }
    }

    /**
     * @throws InvalidBotActionException
     */
    public function callbackQueryHandle() : void
    {
        throw new InvalidBotActionException();
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}
