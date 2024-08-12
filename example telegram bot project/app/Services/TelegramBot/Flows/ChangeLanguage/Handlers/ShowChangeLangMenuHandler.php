<?php

namespace App\Services\TelegramBot\Flows\ChangeLanguage\Handlers;

use App\Services\TelegramBot\Flows\ChangeLanguage\Messages\ShowLanguagesMenuMessage;
use App\Services\TelegramBot\Handlers\AbstractHandler;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\Services\TelegramBotHelperService;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ShowChangeLangMenuHandler extends AbstractHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        private readonly TelegramBotHelperService $telegramBotHelperService,
    ) {
        parent::__construct($telegram);
    }

    /**
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $user = $this->telegramBotHelperService->getUser($this->update, $this->telegram->getBot());

        $message = app(ShowLanguagesMenuMessage::class);

        $this->telegram->sendMessage(array_merge([
                'chat_id' => $user->telegram_chat_id,
            ], $message($user)));

        parent::handle();
    }
}
