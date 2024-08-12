<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\EnteredTransactionIDHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\PayExchangeRequestHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowEnterPaymentTransactionIDHandler;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;

class AwaitEnterPaymentTransactionIDState extends ExchangeRequestFlowState
{
    /**
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function afterChangeHandle() : void
    {
        $handler = app()->make(ShowEnterPaymentTransactionIDHandler::class, [
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
     */
    public function messageHandle() : void
    {
        $handler = app()->make(EnteredTransactionIDHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);
        $handler->setNext(app()->make(PayExchangeRequestHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]));

        $handler->handle();

        $this->exchangeRequest->changeState(app(PaidExchangeRequestState::class));
    }
}
