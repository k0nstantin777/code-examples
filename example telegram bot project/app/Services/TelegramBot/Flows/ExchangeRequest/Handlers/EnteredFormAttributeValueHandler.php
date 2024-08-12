<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class EnteredFormAttributeValueHandler extends ExchangeRequestProcessingHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        ExchangeRequest $exchangeRequest,
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
    ) {
        parent::__construct($telegram, $exchangeRequest);
        $this->exchangeRequest = $exchangeRequest;
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function handle(): void
    {
        $value = $this->update->message->text;

        $formAttributes = $this->exchangeRequest->getFilledFormAttributes();

        $formAttribute = $this->telegramBotExchangeRequestService->getNextRequiredFormAttribute(
            $this->exchangeRequest
        );

        if ($formAttribute) {
            $formAttributes[$formAttribute->code] = $value;
            $this->exchangeRequest->setFilledFormAttributes($formAttributes);
        }

        parent::handle();
    }
}
