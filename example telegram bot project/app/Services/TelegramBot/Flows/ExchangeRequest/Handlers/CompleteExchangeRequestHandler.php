<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Enums\MessageVariable;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\ShowCreateNewExchangeRequestMessage;
use App\Services\TelegramBot\Messages\ShowSimpleMessage;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use App\Services\TelegramBot\Services\TelegramBotApi;
use Telegram\Bot\Exceptions\TelegramSDKException;

class CompleteExchangeRequestHandler extends ExchangeRequestProcessingHandler
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
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function handle(): void
    {
        $remoteExchangeRequest = $this->telegramBotExchangeRequestService->getRemoteExchangeRequest(
            $this->exchangeRequest,
        );

        $status = $remoteExchangeRequest->statusString;

        $message = app(ShowSimpleMessage::class);

        $text = $this->messageSettingsService->getFormattedByCode(MessageCode::EXCHANGE_REQUEST_COMPLETED, [
                MessageVariable::STATUS_NAME() => $status,
            ])  . "\n";

        if ($remoteExchangeRequest->commentForCustomer) {
            $text .= $this->messageSettingsService->getFormattedByCode(MessageCode::OPERATOR_COMMENTED, [
                MessageVariable::COMMENT_TEXT() => escapeMarkdownV2BotChars($remoteExchangeRequest->commentForCustomer),
            ]);
        }

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($text)));

        $message = app(ShowCreateNewExchangeRequestMessage::class);

        $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->exchangeRequest->getUser()->telegram_chat_id,
        ], $message($this->exchangeRequest)));

        parent::handle();
    }
}
