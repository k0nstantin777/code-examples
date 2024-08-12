<?php

namespace App\Services\TelegramBot\Services;

use App\Services\BaseService;
use App\Services\TelegramBot\Buttons\BackActionButton;
use App\Services\TelegramBot\Buttons\BaseInlineButton;
use App\Services\TelegramBot\Buttons\NextActionButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\CancelRemoteExchangeRequestButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\CreateNewExchangeRequestButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\PayRemoteExchangeRequestButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SendExchangeRequestToServerButton;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Button;

class TelegramBotButtonService extends BaseService
{
    public function __construct(
        protected TelegramBotApi $api,
    ) {
    }

    /**
     * @throws TelegramSDKException
     */
    public function isButtonPressed(BaseInlineButton $button) : bool
    {
        $callbackQuery = $this->api->getWebhookUpdate()->callbackQuery;

        if (!$callbackQuery) {
            return false;
        }

        return $button->isPressed($callbackQuery->data);
    }

    /**
     * @throws BindingResolutionException
     */
    public function makeBackButton(string $name = 'Back')
    {
        return app(BackActionButton::class)->make($name);
    }

    /**
     * @throws BindingResolutionException
     */
    public function makeNextButton(string $name = 'Next') : Button
    {
        return app(NextActionButton::class)->make($name);
    }

    /**
     * @throws BindingResolutionException
     */
    public function makeCreateNewExchangeRequestButton(string $name = 'Start over')  : Button
    {
        return app(CreateNewExchangeRequestButton::class)->make($name);
    }

    /**
     * @throws BindingResolutionException
     */
    public function makeSendExchangeRequestButton(string $name = 'Send') : Button
    {
        return app(SendExchangeRequestToServerButton::class)->make($name);
    }

    public function makeCancelExchangeRequestButton(string $exchangeRequestId, string $name = 'Cancel') : Button
    {
        return app(CancelRemoteExchangeRequestButton::class)->makeWithId($name, $exchangeRequestId);
    }

    /**
     * @throws BindingResolutionException
     */
    public function makePayExchangeRequestButton(string $name = 'I paid')
    {
        return app(PayRemoteExchangeRequestButton::class)->make($name);
    }

    /**
     * @throws TelegramSDKException
     */
    public function isBackBtnPressed() : bool
    {
        return $this->isButtonPressed(app(BackActionButton::class));
    }

    /**
     * @throws TelegramSDKException
     */
    public function isNextBtnPressed() : bool
    {
        return $this->isButtonPressed(app(NextActionButton::class));
    }

    /**
     * @throws TelegramSDKException
     */
    public function isCreateNewExchangeRequestBtnPressed() : bool
    {
        return $this->isButtonPressed(app(CreateNewExchangeRequestButton::class));
    }

    /**
     * @throws TelegramSDKException
     */
    public function isSendExchangeRequestBtnPressed() : bool
    {
        return $this->isButtonPressed(app(SendExchangeRequestToServerButton::class));
    }
}
