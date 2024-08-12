<?php

namespace App\Services\TelegramBot\Jobs;

use App\Domains\User\Models\User;
use App\Services\Exchanger\Enums\ExchangeRequestStatus;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\ExchangeRequest\ExchangeRequestService;
use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\Horizon\QueueName;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\CompleteExchangeRequestState;
use App\Services\TelegramBot\Jobs\Middleware\ExchangerRateLimited;
use App\Services\TelegramBot\Jobs\Middleware\SetAppLanguageByUser;
use App\Services\TelegramBot\Jobs\Middleware\SetTelegramBotApiByUser;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class TrackActiveRemoteExchangeRequest implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    private ExchangeRequestService $exchangeRequestService;
    private TelegramBotExchangeRequestService $telegramBotExchangeRequestService;

    public function __construct($user)
    {
        $this->user = $user;
        $this->exchangeRequestService = app(ExchangeRequestService::class);
        $this->telegramBotExchangeRequestService = app(TelegramBotExchangeRequestService::class);

        $this->onQueue(QueueName::TRACK_EXCHANGE_REQUEST);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function handle() : void
    {
        $exchangeRequest = $this->telegramBotExchangeRequestService->getByUserId($this->user->id);

        $activeExchangeRequest = $this->telegramBotExchangeRequestService->refreshRemoteExchangeRequest(
            $exchangeRequest
        );

        $this->syncExchangeRequest($exchangeRequest, $activeExchangeRequest);

        if ($this->isComplete($activeExchangeRequest)) {
            $exchangeRequest->changeState(app(CompleteExchangeRequestState::class));
        }

        $this->telegramBotExchangeRequestService->save($exchangeRequest);
    }

    private function isComplete(ActiveExchangeRequest $activeExchangeRequest) : bool
    {
        return $activeExchangeRequest->status === ExchangeRequestStatus::COMPLETED ||
            $activeExchangeRequest->status === ExchangeRequestStatus::CANCELLED ||
            $activeExchangeRequest->status === ExchangeRequestStatus::SUSPENDED;
    }

    private function syncExchangeRequest(ExchangeRequest $exchangeRequest, ActiveExchangeRequest $activeExchangeRequest)
    {
        $exchangeRequest->setPaymentFormData(
            array_merge(
                $exchangeRequest->getPaymentFormData(),
                $activeExchangeRequest->paymentFormData,
            )
        );

        $attributes = $exchangeRequest->getFilledFormAttributes();
        foreach ($activeExchangeRequest->attributes as $attribute) {
            $attributes[$attribute->code] = $attribute->value;
        }

        $exchangeRequest->setFilledFormAttributes($attributes);
    }

    public function uniqueId() : int
    {
        return $this->user->id;
    }

    public function middleware() : array
    {
        return [
            ExchangerRateLimited::class,
            new SetTelegramBotApiByUser($this->user),
            new SetAppLanguageByUser($this->user)
        ];
    }
}
