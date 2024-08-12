<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\CustomerExtendedService;
use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ShowSelectedExchangeDirectionRatesMessage implements SendableMessage
{
    public function __construct(
        private readonly CustomerExtendedService $customerExtendedService,
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
    ) {
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function __invoke(...$params): array
    {
        /** @var ExchangeRequest $exchangeRequest */
        [$exchangeRequest] = $params;

        $exchangeDirection = $this->telegramBotExchangeRequestService->getExchangeDirection($exchangeRequest);

        $text =  __('Exchange rate') . ": *:given_currency* / *:received_currency* \n";
        $text = str_replace(
            ':given_currency',
            $exchangeDirection->givenCurrencyRate . ' ' . $exchangeDirection->givenCurrency->code,
            $text
        );

        $text = str_replace(
            ':received_currency',
            $exchangeDirection->receivedCurrencyRate . ' ' . $exchangeDirection->receivedCurrency->code,
            $text
        );

        if ($exchangeDirection->commissionValue) {
            $text .=  __('Network commission') . ": *:commission* \n";
            $text = str_replace(
                ':commission',
                $exchangeDirection->commissionValue,
                $text
            );
        }

        $customer = null;
        if ($exchangeRequest->getUser()->getExchangerUserIdOrNull()) {
            $customer = $this->customerExtendedService->getByIdOrNull(
                $exchangeRequest->getUser()->getExchangerUserIdOrNull()
            );
        }

        if ($customer && $customer->exchangeBonus) {
            $text .= __('Bonus') . ": *:bonus %* \n";
            $text = str_replace(
                ':bonus',
                $customer->exchangeBonus,
                $text
            );
        }

        return [
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];
    }
}
