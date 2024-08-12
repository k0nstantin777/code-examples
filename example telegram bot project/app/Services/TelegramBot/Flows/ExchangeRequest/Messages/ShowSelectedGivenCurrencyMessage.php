<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Enums\MessageVariable;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetCurrencyRequestDto;
use App\Services\Exchanger\Services\CurrencyService;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Messages\SendableMessage;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ShowSelectedGivenCurrencyMessage implements SendableMessage
{
    public function __construct(
        private readonly CurrencyService $currencyService,
        private readonly MessageSettingsService $messageSettingsService,
    ) {
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function __invoke(...$params): array
    {
        $givenCurrencyId = $params[0];

        $currency = $this->currencyService->get(
            new GetCurrencyRequestDto(
                id: $givenCurrencyId,
            )
        );

        $text = $this->messageSettingsService->getFormattedByCode(MessageCode::GIVEN_CURRENCY_SELECTED, [
            MessageVariable::CURRENCY_NAME() => $currency->name
        ]);

        return [
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
    }
}
