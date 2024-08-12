<?php

namespace App\Services\TelegramBot\Listeners\LanguageSelected;

use App\Domains\User\Services\UserWriteService;
use App\Services\TelegramBot\Events\LanguageSelected;

class UpdateUserLang
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private readonly UserWriteService $userWriteService,
    ) {
        //
    }

    /**
     * Handle the event.
     *
     * @param LanguageSelected $event
     * @return void
     * @throws \Throwable
     */
    public function handle(LanguageSelected $event) : void
    {
        $this->userWriteService->setLanguage($event->user->id, $event->lang);
    }
}
