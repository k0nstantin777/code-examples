<?php

namespace App\Services\TelegramBot\Commands;

use App\Services\TelegramBot\Flows\ChangeLanguage\ChangeLanguageFlow;
use App\Services\TelegramBot\Services\TelegramBotChatLanguageService;
use App\Services\TelegramBot\Services\TelegramBotChatService;
use App\Services\TelegramBot\Services\TelegramBotFlowService;
use App\Services\TelegramBot\Services\TelegramBotHelperService;
use Exception;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class ChangeLanguageCommand extends Command
{
    private readonly TelegramBotChatService $telegramBotChatService;
    private readonly TelegramBotFlowService $telegramBotFlowService;
    private readonly TelegramBotHelperService $telegramBotHelperService;
    private readonly TelegramBotChatLanguageService $telegramBotChatLanguageService;

    /**
     * @var string Command Name
     */
    protected $name = 'language';

    public function __construct()
    {
        $this->telegramBotChatService = app(TelegramBotChatService::class);
        $this->telegramBotFlowService = app(TelegramBotFlowService::class);
        $this->telegramBotHelperService = app(TelegramBotHelperService::class);
        $this->telegramBotChatLanguageService = app(TelegramBotChatLanguageService::class);
    }

    public function getDescription(): string
    {
        return __('Change bot language');
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function handle() : void
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $user = $this->telegramBotHelperService->getUser($this->update, request()->getBot());
        $chat = $this->telegramBotChatService->getByUserId($user->id);

        if (!$chat) {
            $chat = $this->telegramBotChatService->createNewForUser($user);
        }

        $this->telegramBotChatLanguageService->resetForUser($user);
        $this->telegramBotChatService->setFlow($chat, app(ChangeLanguageFlow::class));

        $this->telegramBotFlowService->handleRequest($this->update);
    }
}
