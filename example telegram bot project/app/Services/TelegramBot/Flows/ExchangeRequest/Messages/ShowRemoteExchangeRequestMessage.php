<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\TelegramBot\Messages\SendableMessage;

class ShowRemoteExchangeRequestMessage implements SendableMessage
{
    private const DATE_FORMAT = 'd.m.Y, H:i';

    /**
     * @param mixed ...$params
     * @return array
     */
    public function __invoke(...$params): array
    {
        /** @var ActiveExchangeRequest $exchangeRequest */
        [$activeExchangeRequest] = $params;

        $text = __('Your current exchange request') . ':' . "\n";

        $text .= $this->getNumberText($activeExchangeRequest);
        $text .= $this->getExchangeRateText($activeExchangeRequest);
        $text .= $this->getGivenSumText($activeExchangeRequest);
        $text .= $this->getReceivedSumText($activeExchangeRequest);
        $text .= $this->getCommissionSumText($activeExchangeRequest);
        $text .= $this->getStatusText($activeExchangeRequest);
        $text .= $this->getCreatedDateText($activeExchangeRequest);
        $text .= $this->getExpiredDateText($activeExchangeRequest);

        return [
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];
    }

    private function getNumberText(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        return __('Number') . ': *#' . $activeExchangeRequest->formattedToken . "*\n";
    }

    private function getExchangeRateText(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        $tempString = __('Exchange rate') . ": *:given_currency* / *:received_currency*\n";
        $tempString = str_replace(
            ':given_currency',
            $activeExchangeRequest->givenCurrencyRate . ' ' . $activeExchangeRequest->givenCurrency->code,
            $tempString
        );

        return str_replace(
            ':received_currency',
            $activeExchangeRequest->receivedCurrencyRate . ' ' . $activeExchangeRequest->receivedCurrency->code,
            $tempString
        );
    }

    private function getGivenSumText(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        return sprintf(
            "%s: *%s %s (%s)*\n",
            __('Given'),
            $activeExchangeRequest->givenSum,
            $activeExchangeRequest->givenCurrency->code,
            $activeExchangeRequest->givenCurrency->name
        );
    }

    private function getReceivedSumText(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        return sprintf(
            "%s: *%s %s (%s)*\n",
            __('Received'),
            $activeExchangeRequest->receivedSum,
            $activeExchangeRequest->receivedCurrency->code,
            $activeExchangeRequest->receivedCurrency->name
        );
    }

    private function getCommissionSumText(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        if ($activeExchangeRequest->commissionString) {
            return __('Payment system commission') . ': *' . $activeExchangeRequest->commissionString . "*\n";
        }

        return '';
    }

    private function getStatusText(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        return __('Status') . ': *' . $activeExchangeRequest->statusString . "*\n";
    }

    private function getCreatedDateText(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        return __('Created') . ': *' . $activeExchangeRequest->createdDateString . "*\n";
    }

    private function getExpiredDateText(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        if ($activeExchangeRequest->expiredAt) {
            return __('Expired') .': *' . $activeExchangeRequest->expiredAt->format(self::DATE_FORMAT) . "*\n";
        }

        return '';
    }
}
