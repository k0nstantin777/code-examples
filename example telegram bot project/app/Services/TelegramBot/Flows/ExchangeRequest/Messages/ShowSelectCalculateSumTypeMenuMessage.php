<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectCalculateSumTypeButton;
use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Keyboard\Keyboard;

class ShowSelectCalculateSumTypeMenuMessage implements SendableMessage
{
    public function __construct(
        private readonly MessageSettingsService $messageSettingsService,
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
        /** @var ExchangeRequest $exchangeRequest */
        [$exchangeRequest] = $params;

        $exchangeDirection = $this->telegramBotExchangeRequestService->getExchangeDirection(
            $exchangeRequest,
        );

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $button = app(SelectCalculateSumTypeButton::class);

        $keyboard->row(
            $button->makeForGivenCurrency($exchangeDirection->givenCurrency->code),
            $button->makeForReceivedCurrency($exchangeDirection->receivedCurrency->code),
        );

        return [
            'text' => $this->messageSettingsService->getFormattedByCode(MessageCode::SELECT_CALCULATING_SUM_CURRENCY),
            'reply_markup' => $keyboard,
        ];
    }
}
