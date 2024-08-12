<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ShowExchangeDirectionRateMessage implements SendableMessage
{
    public function __construct(
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
    ) {
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function __invoke(...$params): array
    {
        /**
         * @var ExchangeRequest $exchangeRequest
         */
        [$exchangeRequest] = $params;

        $exchangeDirection = $this->telegramBotExchangeRequestService->getExchangeDirection(
            $exchangeRequest,
        );

        $text = __('Given') . ': *' . $exchangeRequest->getGivenSum() . ' ' . $exchangeDirection->givenCurrency->code . '*' . "\n";
        $text .= __('Received') . ': *' . $exchangeRequest->getReceivedSum() . ' ' . $exchangeDirection->receivedCurrency->code . '*' . "\n";

        return [
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
    }
}
