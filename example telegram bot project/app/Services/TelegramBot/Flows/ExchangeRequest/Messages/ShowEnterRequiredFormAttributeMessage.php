<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Enums\MessageVariable;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\Exchanger\ValueObjects\ExchangeFormAttribute;
use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ShowEnterRequiredFormAttributeMessage implements SendableMessage
{
    public function __construct(
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
        private readonly MessageSettingsService $messageSettingsService,
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

        $exchangeDirection = $this->telegramBotExchangeRequestService->getExchangeDirection($exchangeRequest);
        $requiredFormAttribute = $this->telegramBotExchangeRequestService->getNextRequiredFormAttribute(
            $exchangeRequest
        );
        $code = $requiredFormAttribute->code;

        $text = $this->messageSettingsService->getFormattedByCode(MessageCode::ENTER_FORM_ATTRIBUTE, [
            MessageVariable::ATTRIBUTE_NAME() => $requiredFormAttribute->name
        ]);

        if ($code === ExchangeFormAttribute::REQUISITES_RECEIVED_CURRENCY_CODE) {
            $text .= ' (' . $exchangeDirection->receivedCurrency->exchangePrompt . ')';
        }

        if ($code === ExchangeFormAttribute::REQUISITES_GIVEN_CURRENCY_CODE) {
            $text .= ' (' . $exchangeDirection->givenCurrency->exchangePrompt . ')';
        }

        return [
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
    }
}
