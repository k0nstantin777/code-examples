<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\RejectExchangeRequestRequestDto;
use App\Services\Exchanger\Services\ExchangeRequest\ExchangeRequestService;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Jobs\TrackActiveRemoteExchangeRequest;
use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class RejectExchangeRequestHandler extends ExchangeRequestProcessingHandler
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

        if (!$remoteExchangeRequest || $remoteExchangeRequest->isExpired) {
            throw new InvalidBotActionException(
                $this->messageSettingsService->getFormattedByCode(MessageCode::ERROR_EXCHANGE_REQUEST_NOT_EXIST)
            );
        }

        $this->exchangeRequestService->reject(
            new RejectExchangeRequestRequestDto(
                customer_id: $remoteExchangeRequest->customer->id,
                id: $remoteExchangeRequest->id,
            )
        );

        $message = app(ShowSimpleMessage::class);

        $text = $this->messageSettingsService->getFormattedByCode(MessageCode::EXCHANGE_REQUEST_CANCELLED);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($text)));

        TrackActiveRemoteExchangeRequest::dispatch($this->exchangeRequest->getUser());

        parent::handle();
    }
}
