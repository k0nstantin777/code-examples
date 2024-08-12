<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRequestDto;
use App\Services\Exchanger\Services\ExchangeDirection\ExchangeDirectionService;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectReceivedCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowSelectedExchangeDirectionRatesMessage;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowSelectedReceivedCurrencyMessage;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SelectedReceivedCurrencyHandler extends ExchangeRequestProcessingHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        ExchangeRequest $exchangeRequest,
        private readonly ExchangeDirectionService $exchangeDirectionService,
    ) {
        parent::__construct($telegram, $exchangeRequest);
    }

    /**
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties|InvalidBotActionException
     */
    public function handle(): void
    {
        $awaitedButtonPressed = app(SelectReceivedCurrencyButton::class);
        $data = $this->update->callbackQuery?->data;

        if (!$awaitedButtonPressed->isPressed($data)) {
            throw new InvalidBotActionException();
        }

        $currencyId = $awaitedButtonPressed->getReceivedCurrencyId($data);

        $this->exchangeRequest->setReceivedCurrencyId($currencyId);

        $user = $this->exchangeRequest->getUser();

        $exchangeDirection = $this->exchangeDirectionService->get(
            new GetExchangeDirectionRequestDto(
                id: $awaitedButtonPressed->getExchangeDirectionId($data),
                customer_id: $user->getExchangerUserIdOrNull()
            )
        );

        if (!$exchangeDirection->access->isAllowed) {
            throw new InvalidBotActionException($exchangeDirection->access->cause);
        }

        $this->exchangeRequest->setExchangeDirectionId($exchangeDirection->id);

        $message = app(ShowSelectedReceivedCurrencyMessage::class);

        $this->telegram->sendMessage(array_merge([
                'chat_id' => $user->telegram_chat_id,
            ], $message($currencyId)));

        $message = app(ShowSelectedExchangeDirectionRatesMessage::class);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $user->telegram_chat_id,
        ], $message($this->exchangeRequest)));

        parent::handle();
    }
}
