<?php

namespace App\Services\TelegramBot\Flows\ChangeLanguage\Messages;

use App\Services\Language\LanguageService;
use App\Services\TelegramBot\Flows\ChangeLanguage\Buttons\SelectLanguageButton;
use App\Services\TelegramBot\Messages\SendableMessage;
use Telegram\Bot\Keyboard\Keyboard;

class ShowLanguagesMenuMessage implements SendableMessage
{

    public function __construct(
        private readonly LanguageService $languageService,
    ) {
    }

    public function __invoke(...$params): array
    {
        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $buttons = [];

        $button = app(SelectLanguageButton::class);

        foreach ($this->languageService->getAllowedLanguages() as $langCode => $langLabel) {
            $buttons[] = $button->makeWithCode($langLabel, $langCode);
        }

        $keyboard->row(...$buttons);

        return [
            'text' => __('Choose language'),
            'reply_markup' => $keyboard,
        ];
    }
}
