<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\States;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\CancelRemoteExchangeRequestButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\PayRemoteExchangeRequestButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\PayExchangeRequestHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\RejectExchangeRequestHandler;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowCurrentRemoteExchangeRequestHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Exceptions\TelegramSDKException;

class AwaitCustomerActionForExchangeRequestState extends ExchangeRequestFlowState
{
    /**
     * @throws BindingResolutionException
     * @throws InvalidBotActionException
     * @throws JsonRpcErrorResponseException
     * @throws TelegramSDKException
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function afterChangeHandle() : void
    {
        $handler = app()->make(ShowCurrentRemoteExchangeRequestHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);

        $handler->handle();
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidBotActionException
     * @throws JsonRpcErrorResponseException
     * @throws TelegramSDKException
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function callbackQueryHandle() : void
    {
        $buttonService = app(TelegramBotButtonService::class);

        if ($buttonService->isButtonPressed(app(CancelRemoteExchangeRequestButton::class))) {
            $handler = app()->make(RejectExchangeRequestHandler::class, [
                'exchangeRequest' => $this->exchangeRequest,
            ]);

            $handler->handle();
            $this->exchangeRequest->changeState(app(CancelledExchangeRequestState::class));
            return;
        }

        if (!$buttonService->isButtonPressed(app(PayRemoteExchangeRequestButton::class))) {
            throw new InvalidBotActionException();
        }

        $receivedRequisites = $this->exchangeRequest->getReceivedRequisites();
        $formData = $this->exchangeRequest->getPaymentFormData();

        if (!isset($formData['address']) ||
            !$receivedRequisites
        ) {
            $messageService = app(MessageSettingsService::class);

            throw new InvalidBotActionException(
                $messageService->getFormattedByCode(MessageCode::ERROR_PAYMENT_FORM_ERROR),
            );
        }

        if (!isset($formData['transaction_id'])) {
            $this->exchangeRequest->changeState(app(AwaitEnterPaymentTransactionIDState::class));
            return;
        }

        $handler = app()->make(PayExchangeRequestHandler::class, [
            'exchangeRequest' => $this->exchangeRequest,
        ]);

        $handler->handle();

        $this->exchangeRequest->changeState(app(PaidExchangeRequestState::class));
    }

    /**
     * @throws InvalidBotActionException
     */
    public function messageHandle() : void
    {
        throw new InvalidBotActionException();
    }
}
