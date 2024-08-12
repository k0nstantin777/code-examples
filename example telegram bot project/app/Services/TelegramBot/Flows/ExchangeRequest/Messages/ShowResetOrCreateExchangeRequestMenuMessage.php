<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Keyboard\Keyboard;

class ShowResetOrCreateExchangeRequestMenuMessage implements SendableMessage
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
            $this->telegramBotButtonService->makeSendExchangeRequestButton(__('Send')),
        );

        return [
            'text' => __('Proceed') . '?',
            'reply_markup' => $keyboard,
        ];
    }
}
