<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\ListExchangeDirectionsRequestDto;
use App\Services\Exchanger\Services\ExchangeDirection\ExchangeDirectionService;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionListItem;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectGivenCurrencyButton;
use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Keyboard\Keyboard;

class ShowGivenCurrenciesMessage implements SendableMessage
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

        $exchangeDirections = $this->exchangeDirectionService->getAll(
            new ListExchangeDirectionsRequestDto(
                customer_id: $exchangeRequest->getUser()->getExchangerUserIdOrNull(),
                telegram_bot_name: $this->telegramBotApi->getBot()->name,
            )
        );

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $items = $exchangeDirections->unique(
            fn(ExchangeDirectionListItem $exchangeDirection) => $exchangeDirection->givenCurrency->id,
        )->sortBy(
            fn(ExchangeDirectionListItem $exchangeDirection) => $exchangeDirection->givenCurrency->position
        );

        foreach ($items->split(ceil($items->count() / self::MAX_BUTTONS_IN_ONE_ROW)) as $itemsGroup) {
            $row = [];
            foreach ($itemsGroup as $exchangeDirection) {
                $button = app(SelectGivenCurrencyButton::class);
                $row[] = $button->make(
                    $exchangeDirection->givenCurrency->name,
                    ['id' => $exchangeDirection->givenCurrency->id]
                );
            }
            $keyboard->row(...$row);
        }

        return [
            'text' => $this->messageSettingsService->getFormattedByCode(MessageCode::SELECT_GIVEN_CURRENCY),
            'reply_markup' => $keyboard,
        ];
    }
}
