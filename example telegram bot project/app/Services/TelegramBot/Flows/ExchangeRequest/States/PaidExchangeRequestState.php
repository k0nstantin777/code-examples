<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\TelegramBot\Exceptions\InvalidBotActionException;

class PaidExchangeRequestState extends ExchangeRequestFlowState
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
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}
