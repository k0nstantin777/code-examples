<?php

namespace App\Services\Exchanger\Services\Settings;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\ListSettingsRequestDto;
use App\Services\Language\LanguageService;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class MessageSettingsService
{
    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly LanguageService $languageService,
        private readonly TelegramBotApi $telegramBotApi
    ) {
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getAll() : array
    {
        $code = 'telegram_bot.messages';
        if ($this->telegramBotApi->getBot()->name === 'simple_exchanger_telegram_bot') {
            $code = 'simple_telegram_bot.messages';
        }

        $settings = $this->settingsService->getList(new ListSettingsRequestDto(
            codes : [
                $code
            ]
        ));

        if ($settings->isEmpty()) {
            return [];
        }

        return json_decode($settings->first()->value, true);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getByCodeAndLang(MessageCode $code, string $lang = 'ru') : string
    {
        $messages = $this->getAll();

        if (!isset($messages[$code()][$lang])) {
            return '';
        }

        return $messages[$code()][$lang];
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getFormattedByCode(MessageCode $code, array $replace = []) : string
    {
        $message = $this->getByCodeAndLang($code, $this->languageService->getAppLanguage());

        return strtr($message, $replace);
    }
}
