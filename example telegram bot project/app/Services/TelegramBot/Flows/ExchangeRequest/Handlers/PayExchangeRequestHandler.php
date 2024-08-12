<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\PayExchangeRequestRequestDto;
use App\Services\Exchanger\Services\ExchangeRequest\ExchangeRequestService;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class PayExchangeRequestHandler extends ExchangeRequestProcessingHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        ExchangeRequest $exchangeRequest,
        private readonly ExchangeRequestService $exchangeRequestService,
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
        private readonly MessageSettingsService $messageSettingsService,
    ) {
        parent::__construct($telegram, $exchangeRequest);
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     * @throws TelegramSDKException|InvalidBotActionException
     */
    public function handle(): void
    {
        $remoteExchangeRequest = $this->telegramBotExchangeRequestService->getRemoteExchangeRequest(
            $this->exchangeRequest
        );

        if (!$remoteExchangeRequest || !$remoteExchangeRequest->isPayable) {
            throw new InvalidBotActionException(
                $this->messageSettingsService->getFormattedByCode(MessageCode::ERROR_EXCHANGE_REQUEST_NOT_EXIST)
            );
        }

        $receivedRequisites = $this->exchangeRequest->getReceivedRequisites();
        $formData = $this->exchangeRequest->getPaymentFormData();
        if (!isset($formData['address']) || !isset($formData['transaction_id']) ||
            !$receivedRequisites
        ) {
            throw new InvalidBotActionException(
                $this->messageSettingsService->getFormattedByCode(MessageCode::ERROR_PAYMENT_FORM_ERROR)
            );
        }

        $this->exchangeRequestService->pay(
            new PayExchangeRequestRequestDto(
                customer_id: $remoteExchangeRequest->customer->id,
                id: $remoteExchangeRequest->id,
                transaction_id: $formData['transaction_id'],
                payment_address: $formData['address']
            )
        );

        $message = app(ShowSimpleMessage::class);

        $text = $this->messageSettingsService->getFormattedByCode(MessageCode::EXCHANGE_REQUEST_MARKED_AS_PAID);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($text)));

        parent::handle();
    }
}
