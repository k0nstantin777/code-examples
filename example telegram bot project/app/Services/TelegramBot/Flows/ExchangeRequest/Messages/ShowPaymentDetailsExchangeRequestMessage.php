<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Messages;

use App\Services\Exchanger\Enums\MessageCode;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\Settings\MessageSettingsService;
use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\PaymentFormSteps\ExternalPaymentForm;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\PaymentFormSteps\InternalPaymentForm;
use App\Services\TelegramBot\Flows\ExchangeRequest\Messages\PaymentFormSteps\PaymentForm;
use App\Services\TelegramBot\Messages\SendableMessage;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\Keyboard\Keyboard;

class ShowPaymentDetailsExchangeRequestMessage implements SendableMessage
{
    private const PAYMENT_FORMS = [
        'external_payment_form' => ExternalPaymentForm::class,
    ];

    private const DEFAULT_PAYMENT_FORM = InternalPaymentForm::class;

    public function __construct(
        private readonly TelegramBotButtonService $telegramBotButtonService,
        private readonly MessageSettingsService $messageSettingsService,
    ) {
    }

    /**
     * @param mixed ...$params
     * @return array
     * @throws BindingResolutionException
     * @throws InvalidBotActionException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function __invoke(...$params): array
    {
        /** @var ActiveExchangeRequest $activeExchangeRequest */
        [$activeExchangeRequest] = $params;

        $receivedRequisites = $activeExchangeRequest->getReceivedRequisites();
        if (!isset($activeExchangeRequest->paymentFormData['address']) ||
            !$receivedRequisites
        ) {
            throw new InvalidBotActionException(
                $this->messageSettingsService->getFormattedByCode(MessageCode::ERROR_PAYMENT_FORM_ERROR)
            );
        }

        $text = $this->messageSettingsService->getFormattedByCode(MessageCode::PAYMENT_INSTRUCTIONS_HEAD) . "\n";
        $text .= $this->getPaymentStepsTest($activeExchangeRequest);

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard->row($this->telegramBotButtonService->makePayExchangeRequestButton(__('I paid')));

        return [
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ];
    }

    private function getPaymentStepsTest(ActiveExchangeRequest $activeExchangeRequest) : string
    {
        $paymentForm = $this->getPaymentForm($activeExchangeRequest);

        return $paymentForm($activeExchangeRequest);
    }

    private function getPaymentForm(ActiveExchangeRequest $activeExchangeRequest) : PaymentForm
    {
        $paymentFormData = $activeExchangeRequest->paymentFormData;

        if (isset($paymentFormData['component']) && isset(self::PAYMENT_FORMS[$paymentFormData['component']])) {
            return app(self::PAYMENT_FORMS[$paymentFormData['component']]);
        }

        return app(self::DEFAULT_PAYMENT_FORM);
    }
}
