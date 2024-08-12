<?php

namespace App\Services\TelegramBot\Jobs;

use App\Domains\User\Services\UserReadService;
use App\Services\Horizon\QueueName;
use App\Services\TelegramBot\Events\ChatLogout;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\CompleteExchangeRequestState;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackActiveUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
        private readonly UserReadService $userReadService,
    ) {
        $this->onQueue(QueueName::TRACK_USER);
    }


    public function handle() : void
    {
        $activeUsers = $this->userReadService->getAllActive();

        foreach ($activeUsers as $user) {
            $exchangeRequest = $this->telegramBotExchangeRequestService->getByUserId($user->id);

            if (!$exchangeRequest) {
                continue;
            }

            if (false === $this->isValidSession($exchangeRequest)) {
                ChatLogout::dispatch($user);
                continue;
            }

            if ($this->isCompleteExchangeRequest($exchangeRequest)) {
                continue;
            }

            if ($this->isCreatedExchangeRequest($exchangeRequest)) {
                TrackActiveRemoteExchangeRequest::dispatch($user);
                continue;
            }

            if ($this->isSelectedExchangeDirection($exchangeRequest)) {
                TrackExchangeRate::dispatch($user);
            }
        }
    }

    private function isValidSession(ExchangeRequest $exchangeRequest) : bool
    {
        return !$exchangeRequest->getRemoteId() || $exchangeRequest->getUser()->getExchangerUserIdOrNull();
    }

    private function isCompleteExchangeRequest(ExchangeRequest $exchangeRequest) : bool
    {
        return get_class($exchangeRequest->getState()) === CompleteExchangeRequestState::class;
    }

    private function isCreatedExchangeRequest(ExchangeRequest $exchangeRequest) : bool
    {
        return (bool) $exchangeRequest->getRemoteId();
    }

    private function isSelectedExchangeDirection(ExchangeRequest $exchangeRequest) : bool
    {
        return (bool) $exchangeRequest->getExchangeDirectionId();
    }
}
