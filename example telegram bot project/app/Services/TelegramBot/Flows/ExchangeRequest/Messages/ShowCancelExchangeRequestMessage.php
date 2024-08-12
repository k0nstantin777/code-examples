<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Telegram\Bot\Keyboard\Keyboard;

class ShowCancelExchangeRequestMessage implements SendableMessage
{
    public function __construct(
        private readonly TelegramBotButtonService $telegramBotButtonService,
    ) {
    }

    /**
     * @param mixed ...$params
     * @return array
     */
    public function __invoke(...$params): array
    {
        /** @var ActiveExchangeRequest $activeExchangeRequest */
        [$activeExchangeRequest] = $params;

        $text = __('For cancel order, click the button') .  ' *"' . __('Cancel') . '"*' . "\n";

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard->row($this->telegramBotButtonService->makeCancelExchangeRequestButton(
            $activeExchangeRequest->id,
            __('Cancel')
        ));

        return [
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ];
    }
}
