<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitSelectGivenCurrencyState;
use App\Services\TelegramBot\Flows\Flow;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\Services\TelegramBotHelperService;
use App\Services\TelegramBot\Services\TelegramBotStateService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Telegram\Bot\Objects\Update;

class ExchangeRequestProcessingFlow extends Flow
{
    public function __construct(
        protected TelegramBotExchangeRequestService $exchangeRequestService,
        protected TelegramBotHelperService $telegramBotHelperService,
        protected TelegramBotStateService $telegramBotStateService,
        protected TelegramBotApi $telegramBotApi,
    ) {
    }

    public function handleRequest(Update $update) : void
    {
        $this->updateExchangeRequest($update);
    }

    public function updateExchangeRequest(Update $update) : void
    {
        $user = $this->telegramBotHelperService->getUser($update, $this->telegramBotApi->getBot());
        $exchangeRequest = $this->exchangeRequestService->getByUserId($user->id);

        if (!$exchangeRequest) {
            $this->createNewExchangeRequest($user);
            return;
        }

        $state = $exchangeRequest->getState();

        $this->telegramBotStateService->stateProcessing($state, $update);

        $this->exchangeRequestService->save($state->getExchangeRequest());
    }

    public function createNewExchangeRequest(User $user)
    {
        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->changeState(app(AwaitSelectGivenCurrencyState::class));

        $this->exchangeRequestService->save($exchangeRequest);
    }
}
