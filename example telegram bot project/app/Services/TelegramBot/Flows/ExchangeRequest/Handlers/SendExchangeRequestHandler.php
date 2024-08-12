<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\CreateExchangeRequestRequestDto;
use App\Services\Exchanger\Services\ExchangeRequest\ExchangeRequestService;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SendExchangeRequestHandler extends ExchangeRequestProcessingHandler
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
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $customerEmail = $this->telegramBotExchangeRequestService->getCustomerEmail(
            $this->exchangeRequest
        );

        $exchangeRequestToken = $this->exchangeRequestService->create(
            new CreateExchangeRequestRequestDto(
                customer_id: $this->exchangeRequest->getUser()->getExchangerUserIdOrNull(),
                customer_email: $customerEmail,
                exchange_direction_id: $this->exchangeRequest->getExchangeDirectionId(),
                given_sum: $this->exchangeRequest->getGivenSum(),
                received_sum: $this->exchangeRequest->getReceivedSum(),
                commission: $this->exchangeRequest->getCommission(),
                attributes: $this->exchangeRequest->getFilledFormAttributes(),
            )
        );

        $this->exchangeRequest->setRemoteId($exchangeRequestToken);

        $remoteExchangeRequest = $this->telegramBotExchangeRequestService->getRemoteExchangeRequest(
            $this->exchangeRequest
        );

        $this->exchangeRequest->setPaymentFormData($remoteExchangeRequest->paymentFormData);
        $this->exchangeRequest->setEmail($customerEmail);

        $message = app(ShowSimpleMessage::class);

        $text = $this->messageSettingsService->getFormattedByCode(MessageCode::EXCHANGE_REQUEST_CREATED);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($text)));

        parent::handle();
    }
}
