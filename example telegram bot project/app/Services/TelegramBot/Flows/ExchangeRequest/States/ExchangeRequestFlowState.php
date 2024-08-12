<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\TelegramBot\Flows\State;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;

abstract class ExchangeRequestFlowState implements State
{
    protected ?ExchangeRequest $exchangeRequest = null;

    public function getExchangeRequest() : ExchangeRequest
    {
        return $this->exchangeRequest;
    }

    public function setExchangeRequest(ExchangeRequest $exchangeRequest) : void
    {
        $this->exchangeRequest = $exchangeRequest;
    }
}
