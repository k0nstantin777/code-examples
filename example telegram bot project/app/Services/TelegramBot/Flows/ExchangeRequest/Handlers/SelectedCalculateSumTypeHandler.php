<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectCalculateSumTypeButton;
use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SelectedCalculateSumTypeHandler extends ExchangeRequestProcessingHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        ExchangeRequest $exchangeRequest,
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
        $button = app(SelectCalculateSumTypeButton::class);
        $calculateType = $button->getCalculateSumType($this->update->callbackQuery->data);

        $this->exchangeRequest->setCalculateType(CalculateSumType::tryFrom($calculateType));

        $exchangeDirection = $this->telegramBotExchangeRequestService->getExchangeDirection(
            $this->exchangeRequest
        );

        if ($calculateType === CalculateSumType::GIVEN_CURRENCY->value) {
            $min = $exchangeDirection->givenMinSum > 0 ? $exchangeDirection->givenMinSum : '';
            $max = $exchangeDirection->givenMaxSum > 0 ? $exchangeDirection->givenMaxSum : '';
            $currencyCode = $exchangeDirection->givenCurrency->code;
            $currencyName = $exchangeDirection->givenCurrency->name;
        } else {
            $min = $exchangeDirection->receivedMinSum > 0 ? $exchangeDirection->receivedMinSum : '';
            $max = $exchangeDirection->receivedMaxSum > 0 ? $exchangeDirection->receivedMaxSum: '';
            $currencyCode = $exchangeDirection->receivedCurrency->code;
            $currencyName = $exchangeDirection->receivedCurrency->name;
        }

        $text =  __('Enter :currency_code (:currency_name) amount', [
            'currency_code' => $currencyCode,
            'currency_name' => $currencyName,
        ]);
        $text .= $min ? ' ' . __('from') .' ' . $min . ' ' . $currencyCode : '' ;
        $text .= $max ? ' ' . __('up to') .' ' . $max . ' ' . $currencyCode : '' ;

        $text = escapeMarkdownV2BotChars(trim($text));

        $message = app(ShowSimpleMessage::class);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($text)));

        parent::handle();
    }
}
