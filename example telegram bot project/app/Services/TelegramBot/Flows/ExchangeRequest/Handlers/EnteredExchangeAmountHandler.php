<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRateRequestDto;
use App\Services\Exchanger\Services\ExchangeDirection\ExchangeDirectionRateService;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowExchangeDirectionRateMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class EnteredExchangeAmountHandler extends ExchangeRequestProcessingHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        ExchangeRequest $exchangeRequest,
        private readonly ExchangeDirectionRateService $exchangeDirectionRateService,
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
    ) {
        parent::__construct($telegram, $exchangeRequest);
    }

    /**
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function handle(): void
    {
        $amount = $this->update->message->text;
        $exchangeDirection = $this->telegramBotExchangeRequestService->getExchangeDirection(
            $this->exchangeRequest,
        );

        if ($this->exchangeRequest->getCalculateType() === CalculateSumType::GIVEN_CURRENCY) {
            $exchangeDirectionRate = $this->exchangeDirectionRateService->calculateReceived(
                new GetExchangeDirectionRateRequestDto(
                    id: $exchangeDirection->id,
                    given_sum: $amount,
                    customer_id: $this->exchangeRequest->getUser()->getExchangerUserIdOrNull(),
                ),
            );
        } else {
            $exchangeDirectionRate = $this->exchangeDirectionRateService->calculateGiven(
                new GetExchangeDirectionRateRequestDto(
                    id: $exchangeDirection->id,
                    received_sum: $amount,
                    customer_id: $this->exchangeRequest->getUser()->getExchangerUserIdOrNull(),
                ),
            );
        }

        $this->exchangeRequest->setGivenSum($exchangeDirectionRate->givenSum);
        $this->exchangeRequest->setReceivedSum($exchangeDirectionRate->receivedSum);
        $this->exchangeRequest->setCommission($exchangeDirectionRate->commission);

        $message = app(ShowExchangeDirectionRateMessage::class);

        $this->telegram->sendMessage(array_merge([
                'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
            ], $message($this->exchangeRequest)));

        parent::handle();
    }
}
