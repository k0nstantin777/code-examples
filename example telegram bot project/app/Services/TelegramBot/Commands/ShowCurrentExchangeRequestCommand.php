<?php

namespace App\Services\TelegramBot\Commands;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\ExchangeRequestProcessingFlow;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowCurrentExchangeRequestHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowCurrentRemoteExchangeRequestHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitConfirmExchangeRequestState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitCustomerActionForExchangeRequestState;
use App\Services\TelegramBot\Services\TelegramBotChatService;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\Services\TelegramBotHelperService;
use Exception;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class ShowCurrentExchangeRequestCommand extends Command
{
    private readonly TelegramBotExchangeRequestService $exchangeRequestService;
    private readonly TelegramBotHelperService $telegramBotHelperService;
    private readonly MessageSettingsService $messageSettingsService;
    private readonly TelegramBotChatService $telegramBotChatService;

    public function __construct()
    {
        $this->exchangeRequestService = app(TelegramBotExchangeRequestService::class);
        $this->telegramBotHelperService = app(TelegramBotHelperService::class);
        $this->messageSettingsService = app(MessageSettingsService::class);
        $this->telegramBotChatService = app(TelegramBotChatService::class);
    }

    /**
     * @var string Command Name
     */
    protected $name = 'show_current_exchange';

    public function getDescription(): string
    {
        return __('Show current exchange request');
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function handle() : void
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $user = $this->telegramBotHelperService->getUser($this->update, request()->getBot());

        $exchangeRequest = $this->exchangeRequestService->getByUserId($user->id);

        if (!$exchangeRequest) {
            throw new InvalidBotActionException(
                $this->messageSettingsService->getFormattedByCode(MessageCode::ERROR_EXCHANGE_NOT_STARTED_YET)
            );
        }

        $currentState = $exchangeRequest->getState();

        if (!$exchangeRequest->getRemoteId()) {
            if (get_class($currentState) !== AwaitConfirmExchangeRequestState::class) {
                $handler = app()->make(ShowCurrentExchangeRequestHandler::class, [
                    'exchangeRequest' => $exchangeRequest,
                ]);
                $handler->handle();
            }
        } else {
            if (get_class($currentState) !== AwaitCustomerActionForExchangeRequestState::class) {
                $handler = app()->make(ShowCurrentRemoteExchangeRequestHandler::class, [
                    'exchangeRequest' => $exchangeRequest,
                ]);
                $handler->handle();
            }
        }

        $exchangeRequest->changeState($currentState);
        $this->exchangeRequestService->save($exchangeRequest);

        $chat = $this->telegramBotChatService->getByUserId($user->id);

        if (!$chat) {
            $chat = $this->telegramBotChatService->createNewForUser($user);
        }

        $this->telegramBotChatService->setFlow($chat, app(ExchangeRequestProcessingFlow::class));
    }
}
