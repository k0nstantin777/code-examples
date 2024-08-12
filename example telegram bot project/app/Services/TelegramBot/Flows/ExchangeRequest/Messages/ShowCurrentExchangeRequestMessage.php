<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ShowCurrentExchangeRequestMessage implements SendableMessage
{
    public function __construct(
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
        private readonly MessageSettingsService $messageSettingsService,
    ) {
    }

    /**
     * @param mixed ...$params
     * @return array
     * @throws InvalidBotActionException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function __invoke(...$params): array
    {
        /**
         * @var ExchangeRequest $exchangeRequest
         */
        [$exchangeRequest] = $params;

        $exchangeDirection = $this->telegramBotExchangeRequestService->getExchangeDirection(
            $exchangeRequest
        );

        if (!$exchangeDirection || !$exchangeRequest->getGivenSum() || !$exchangeRequest->getReceivedSum()) {
            throw new InvalidBotActionException(
                $this->messageSettingsService->getFormattedByCode(MessageCode::ERROR_EXCHANGE_NOT_FILLED_YET)
            );
        }

        $text = __('Your current exchange request') . ': ' . "\n";

        $text .= sprintf(
            "%s: *%s %s (%s)*\n",
            __('Given'),
            $exchangeRequest->getGivenSum(),
            $exchangeDirection->givenCurrency->code,
            $exchangeDirection->givenCurrency->name
        );

        $text .= sprintf(
            "%s: *%s %s (%s)*\n",
            __('Received'),
            $exchangeRequest->getReceivedSum(),
            $exchangeDirection->receivedCurrency->code,
            $exchangeDirection->receivedCurrency->name
        );

        $filledAttributes = $exchangeRequest->getFilledFormAttributes();
        foreach ($exchangeDirection->formAttributes as $formAttribute) {
            if (!isset($filledAttributes[$formAttribute->code])) {
                continue;
            }

            $text .= sprintf(
                "%s: *%s*\n",
                $formAttribute->name,
                $filledAttributes[$formAttribute->code],
            );
        }

        return [
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
    }
}
