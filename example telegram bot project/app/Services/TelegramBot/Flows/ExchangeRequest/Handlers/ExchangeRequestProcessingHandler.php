<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\TelegramBot\Handlers\AbstractHandler;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use App\Services\TelegramBot\Services\TelegramBotApi;

abstract class ExchangeRequestProcessingHandler extends AbstractHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        protected ExchangeRequest $exchangeRequest,
    ) {
        parent::__construct($telegram);
    }
}
