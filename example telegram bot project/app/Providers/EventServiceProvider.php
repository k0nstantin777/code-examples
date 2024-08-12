<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
//        Registered::class => [
//            SendEmailVerificationNotification::class,
//        ],
//        \App\Services\TelegramBot\Events\ChatAuthenticated::class => [
//            \App\Services\TelegramBot\Listeners\ChatAuthenticated\UpdateChatExchangeRequest::class
//        ],
//
//        \App\Services\TelegramBot\Events\ChatLogout::class => [
//            \App\Services\TelegramBot\Listeners\ChatLogout\RemoveExchangeRequest::class,
//            \App\Services\TelegramBot\Listeners\ChatLogout\ShowLogoutMessage::class
//        ],
        \App\Services\TelegramBot\Events\LanguageSelected::class => [
            \App\Services\TelegramBot\Listeners\LanguageSelected\SetDefaultChatFlow::class,
            \App\Services\TelegramBot\Listeners\LanguageSelected\UpdateUserLang::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
