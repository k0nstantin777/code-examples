<?php

namespace App\Services\TelegramBot\Services;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class TelegramBotExchangeDirectionService
{
    public function __construct(
        private readonly TelegramBotRemoteExchangeDirectionService $telegramBotRemoteExchangeDirectionService,
    ) {
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getByExchangeRequest(ExchangeRequest $exchangeRequest) : ?ExchangeDirection
    {
        return $this->getRemoteExchangeDirection($exchangeRequest);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getRemoteExchangeDirection(ExchangeRequest $exchangeRequest) : ?ExchangeDirection
    {
        return $this->telegramBotRemoteExchangeDirectionService->get($exchangeRequest);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function refreshRemoteExchangeDirection(ExchangeRequest $exchangeRequest) : ?ExchangeDirection
    {
        return $this->telegramBotRemoteExchangeDirectionService->refresh($exchangeRequest);
    }
}
