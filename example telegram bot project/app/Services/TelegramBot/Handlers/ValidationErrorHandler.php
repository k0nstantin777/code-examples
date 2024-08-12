<?php

namespace App\Services\TelegramBot\Handlers;

use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ValidationErrorHandler extends AbstractHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        private readonly array $errors,
    ) {
        parent::__construct($telegram);
    }

    /**
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $messageObj = app(ShowSimpleMessage::class);

        $text = __('After your last action, got next errors') . ': ' . "\n";

        $i = 1;
        foreach ($this->errors as $messages) {
            foreach ($messages as $message) {
                $text .= escapeMarkdownV2BotChars($i . ') ' . $message) . "\n";
                $i++;
            }
        }

        $text .= __('Repeat your action');

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->update->getChat()->id,
        ], $messageObj($text)));
    }
}
