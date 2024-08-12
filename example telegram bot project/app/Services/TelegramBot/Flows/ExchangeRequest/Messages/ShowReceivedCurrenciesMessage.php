<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\ListExchangeDirectionsRequestDto;
use App\Services\Exchanger\Services\ExchangeDirection\ExchangeDirectionService;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionListItem;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectReceivedCurrencyButton;
use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Keyboard\Keyboard;

class ShowReceivedCurrenciesMessage implements SendableMessage
{
    private const MAX_BUTTONS_IN_ONE_ROW = 3;

    public function __construct(
        private readonly ExchangeDirectionService $exchangeDirectionService,
        private readonly MessageSettingsService $messageSettingsService,
        private readonly TelegramBotApi $telegramBotApi,
    ) {
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws BindingResolutionException
     */
    public function __invoke(...$params): array
    {
        /** @var ExchangeRequest $exchangeRequest */
        $exchangeRequest = $params[0];

        $user = $exchangeRequest->getUser();

        $exchangeDirections = $this->exchangeDirectionService->getAll(
            new ListExchangeDirectionsRequestDto(
                customer_id: $user->getExchangerUserIdOrNull(),
                given_currency_id: $exchangeRequest->getGivenCurrencyId(),
                telegram_bot_name: $this->telegramBotApi->getBot()->name,
            )
        );

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $items = $exchangeDirections->unique(
            fn(ExchangeDirectionListItem $exchangeDirection) => $exchangeDirection->receivedCurrency->id,
        )->sortBy(
            fn(ExchangeDirectionListItem $exchangeDirection) => $exchangeDirection->receivedCurrency->position
        );

        foreach ($items->split(ceil($items->count() / self::MAX_BUTTONS_IN_ONE_ROW)) as $itemsGroup) {
            $row = [];
            foreach ($itemsGroup as $exchangeDirection) {
                $button = app(SelectReceivedCurrencyButton::class);
                $row[] = $button->make(
                    $exchangeDirection->receivedCurrency->name,
                    [
                        SelectReceivedCurrencyButton::CURRENCY_ID_PARAM => $exchangeDirection->receivedCurrency->id,
                        SelectReceivedCurrencyButton::EXCHANGE_DIRECTION_ID_PARAM => $exchangeDirection->id,
                    ]
                );
            }
            $keyboard->row(...$row);
        }

        return [
            'text' => $this->messageSettingsService->getFormattedByCode(MessageCode::SELECT_RECEIVED_CURRENCY),
            'reply_markup' => $keyboard,
        ];
    }
}
