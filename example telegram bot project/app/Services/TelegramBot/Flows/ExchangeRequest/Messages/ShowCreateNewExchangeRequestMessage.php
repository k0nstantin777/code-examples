<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Keyboard\Keyboard;

class ShowCreateNewExchangeRequestMessage implements SendableMessage
{
    public function __construct(
        private readonly TelegramBotButtonService $telegramBotButtonService,
    ) {
    }

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(...$params): array
    {
        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard->row(
            $this->telegramBotButtonService->makeCreateNewExchangeRequestButton(__('Start over')),
        );

        $text = __('To create a new exchange, click on the button below');

        return [
            'text' => $text,
            'reply_markup' => $keyboard,
        ];
    }
}
