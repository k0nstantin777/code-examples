<?php

namespace App\Services\TelegramBot\Flows\ChangeLanguage\Handlers;

use App\Services\Language\Enums\LanguageCode;
use App\Services\Language\LanguageService;
use App\Services\TelegramBot\Events\LanguageSelected;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ChangeLanguage\Buttons\SelectLanguageButton;
use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\ValueObjects\ChatLanguage;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SelectedLanguageHandler extends ChatLanguageProcessingHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        ChatLanguage $chatLanguage,
        private readonly LanguageService $languageService,
    ) {
        parent::__construct($telegram, $chatLanguage);
    }

    /**
     * @throws TelegramSDKException
     * @throws InvalidBotActionException
     */
    public function handle(): void
    {
        $button = app(SelectLanguageButton::class);
        $lang = $button->getLang($this->update->callbackQuery->data);

        if (false === $this->isValidLang($lang)) {
            throw new InvalidBotActionException();
        }

        $langCode = LanguageCode::from($lang);

        $this->chatLanguage->setLang($langCode);

        $user = $this->chatLanguage->getUser();

        LanguageSelected::dispatch($user, $langCode);

        $this->languageService->setAppLanguage($langCode);

        $message = app(ShowSimpleMessage::class);

        $this->telegram->sendMessage(array_merge([
                'chat_id' => $user->telegram_chat_id,
            ], $message(__('Language changed'))));

        parent::handle();
    }

    private function isValidLang(string $lang) : bool
    {
        if (array_key_exists($lang, $this->languageService->getAllowedLanguages())) {
            return true;
        }

        return false;
    }
}
