<?php

namespace App\Services\TelegramBot\Commands;

use App\Services\TelegramBot\Flows\ExchangeRequest\ExchangeRequestProcessingFlow;
use App\Services\TelegramBot\Services\TelegramBotChatService;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\Services\TelegramBotFlowService;
use App\Services\TelegramBot\Services\TelegramBotHelperService;
use Exception;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartExchangeCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'start_exchange';

    public function getDescription(): string
    {
        return __('Create new exchange request');
    }

    private readonly TelegramBotChatService $telegramBotChatService;
    private readonly TelegramBotHelperService $telegramBotHelperService;
    private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService;
    private readonly TelegramBotFlowService $telegramBotFlowService;

    public function __construct()
    {
        $this->telegramBotChatService = app(TelegramBotChatService::class);
        $this->telegramBotHelperService = app(TelegramBotHelperService::class);
        $this->telegramBotExchangeRequestService = app(TelegramBotExchangeRequestService::class);
        $this->telegramBotFlowService = app(TelegramBotFlowService::class);
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

        $this->telegramBotExchangeRequestService->resetForUser($user);
        $this->telegramBotChatService->setFlow($chat, app(ExchangeRequestProcessingFlow::class));

        $this->telegramBotFlowService->handleRequest($this->update);
    }
}
