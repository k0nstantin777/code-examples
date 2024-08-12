<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowCancelExchangeRequestMessage;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowPaymentDetailsExchangeRequestMessage;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowRemoteExchangeRequestMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ShowCurrentRemoteExchangeRequestHandler extends ExchangeRequestProcessingHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        ExchangeRequest $exchangeRequest,
        private readonly TelegramBotExchangeRequestService $telegramBotExchangeRequestService,
        private readonly MessageSettingsService $messageSettingsService,
    ) {
        parent::__construct($telegram, $exchangeRequest);
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidBotActionException
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function handle(): void
    {
        if (!$this->exchangeRequest->getRemoteId()) {
            throw new InvalidBotActionException(
                $this->messageSettingsService->getFormattedByCode(MessageCode::ERROR_EXCHANGE_REQUEST_NOT_EXIST)
            );
        }

        $activeExchangeRequest = $this->telegramBotExchangeRequestService->getRemoteExchangeRequest(
            $this->exchangeRequest
        );

        $message = app(ShowRemoteExchangeRequestMessage::class);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($activeExchangeRequest)));

        if ($activeExchangeRequest->isPayable) {
            $message = app(ShowPaymentDetailsExchangeRequestMessage::class);

            $this->telegram->sendMessage(array_merge([
                'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
            ], $message($activeExchangeRequest)));
        }

        if ($activeExchangeRequest->isRejectable) {
            $message = app(ShowCancelExchangeRequestMessage::class);

            $this->telegram->sendMessage(array_merge([
                'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
            ], $message($activeExchangeRequest)));
        }

        parent::handle();
    }
}
