<?php

namespace App\Services\TelegramBot\Services;

use App\Domains\User\Models\User;
use AshAllenDesign\ShortURL\Classes\Builder;
use AshAllenDesign\ShortURL\Exceptions\ShortURLException;
use Illuminate\Support\Facades\URL;

class TelegramBotLoginService
{
    private const CACHE_KEY = 'login_link_';
    private const CACHE_SECONDS = 5*60;

    public function __construct(
        private readonly Builder $shortUrlBuilder,
    ) {
    }

    /**
     * @throws ShortURLException
     */
    public function getLoginUrl(User $user) : string
    {
        return cache()->remember(self::CACHE_KEY. $user->id, self::CACHE_SECONDS, function () use ($user) {
            return sprintf(
                'Goto [link](%s) and login',
                $this->makeNewLoginUrl($user->telegram_chat_id),
            );
        });
    }

    /**
     * @throws ShortURLException
     */
    private function makeNewLoginUrl(int $chatId) : string
    {
        $exchangerConfig = config('services.exchanger');

        $redirectUrl = URL::temporarySignedRoute(
            'auth-chat',
            now()->addMinutes(30),
            ['chat_id' => $chatId]
        );

        $shorUrl = $this->shortUrlBuilder->destinationUrl($redirectUrl)->make();

        return escapeInsideLinkBotChars(sprintf(
            '%s%s?t=%s&id=%s&redirect_url=%s',
            $exchangerConfig['base_url'],
            $exchangerConfig['login_path'],
            $exchangerConfig['login_source_type'],
            $chatId,
            $shorUrl->default_short_url,
        ));
    }
}
