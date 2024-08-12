<?php

namespace App\Services\TelegramBot\Jobs;

use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRateRequestDto;
use App\Services\Exchanger\Services\ExchangeDirection\ExchangeDirectionRateService;
use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\Horizon\QueueName;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowExchangeDirectionRateMessage;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowSelectedExchangeDirectionRatesMessage;
use App\Services\TelegramBot\Jobs\Middleware\ExchangerRateLimited;
use App\Services\TelegramBot\Jobs\Middleware\SetAppLanguageByUser;
use App\Services\TelegramBot\Jobs\Middleware\SetTelegramBotApiByUser;
use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\Services\TelegramBotExchangeDirectionService;
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
use Telegram\Bot\Exceptions\TelegramSDKException;

class TrackExchangeRate implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    private ExchangeDirectionRateService $exchangeDirectionRateService;
    private TelegramBotExchangeRequestService $telegramBotExchangeRequestService;
    private TelegramBotExchangeDirectionService $telegramBotExchangeDirectionService;
    public int $uniqueFor = 60;

    public function __construct($user)
    {
        $this->user = $user;
        $this->exchangeDirectionRateService = app(ExchangeDirectionRateService::class);
        $this->telegramBotExchangeRequestService = app(TelegramBotExchangeRequestService::class);
        $this->telegramBotExchangeDirectionService = app(TelegramBotExchangeDirectionService::class);

        $this->onQueue(QueueName::TRACK_EXCHANGE_DIRECTION);
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws UnknownProperties
     * @throws ValidationException
     * @throws TelegramSDKException
     */
    public function handle() : void
    {
        $telegramBotApi = app(TelegramBotApi::class);

        $exchangeRequest = $this->telegramBotExchangeRequestService->getByUserId($this->user->id);

        $exchangeDirection = $this->telegramBotExchangeDirectionService->refreshRemoteExchangeDirection(
            $exchangeRequest
        );

        $isUpdateCurrentExchangeRate = $this->updateCurrentExchangeDirectionRate($exchangeRequest, $exchangeDirection);

        $this->telegramBotExchangeRequestService->save($exchangeRequest);

        $message = app(ShowSimpleMessage::class);
        $text = escapeMarkdownV2BotChars('---------------------------') . "\n";

        $telegramBotApi->sendMessage(array_merge([
            'chat_id' => $this->user->telegram_chat_id,
        ], $message($text)));

        $message = app(ShowSimpleMessage::class);
        $text = __('Rates of current exchange has been updated');

        $telegramBotApi->sendMessage(array_merge([
            'chat_id' => $this->user->telegram_chat_id,
        ], $message($text)));

        $message = app(ShowSelectedExchangeDirectionRatesMessage::class);

        if ($isUpdateCurrentExchangeRate) {
            $telegramBotApi->sendMessage(array_merge([
                'chat_id' => $this->user->telegram_chat_id,
            ], $message($exchangeRequest)));

            $message = app(ShowExchangeDirectionRateMessage::class);
        }

        $telegramBotApi->sendMessage(array_merge([
            'chat_id' => $this->user->telegram_chat_id,
        ], $message($exchangeRequest)));

        $message = app(ShowSimpleMessage::class);
        $text = escapeMarkdownV2BotChars('---------------------------') . "\n";

        $telegramBotApi->sendMessage(array_merge([
            'chat_id' => $this->user->telegram_chat_id,
        ], $message($text)));

        $exchangeRequest->changeState($exchangeRequest->getState());
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    private function updateCurrentExchangeDirectionRate(
        ExchangeRequest $exchangeRequest,
        ExchangeDirection $exchangeDirection
    ) : bool {
        $exchangeDirectionRate = null;
        if ($exchangeRequest->getCalculateType() === CalculateSumType::GIVEN_CURRENCY &&
            $exchangeRequest->getGivenSum()
        ) {
            $exchangeDirectionRate = $this->exchangeDirectionRateService->calculateReceived(
                new GetExchangeDirectionRateRequestDto(
                    id: $exchangeDirection->id,
                    given_sum: $exchangeRequest->getGivenSum(),
                    customer_id: $exchangeRequest->getUser()->getExchangerUserIdOrNull(),
                ),
            );
        }

        if (!$exchangeDirectionRate &&
            $exchangeRequest->getCalculateType() === CalculateSumType::RECEIVED_CURRENCY &&
            $exchangeRequest->getReceivedSum()
        ) {
            $exchangeDirectionRate = $this->exchangeDirectionRateService->calculateGiven(
                new GetExchangeDirectionRateRequestDto(
                    id: $exchangeDirection->id,
                    received_sum: $exchangeRequest->getReceivedSum(),
                    customer_id: $exchangeRequest->getUser()->getExchangerUserIdOrNull(),
                ),
            );
        }

        if (!$exchangeDirectionRate) {
            return false;
        }

        $exchangeRequest->setGivenSum($exchangeDirectionRate->givenSum);
        $exchangeRequest->setReceivedSum($exchangeDirectionRate->receivedSum);
        $exchangeRequest->setCommission($exchangeDirectionRate->commission);

        return true;
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
