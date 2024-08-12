<?php

namespace Tests\Feature\Services\TelegramBot\Services;

use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\CreateNewExchangeRequestButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SendExchangeRequestToServerButton;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\Keyboard\Keyboard;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TelegramBotButtonServiceTest extends TestCase
{
    use TelegramBotApiMock;
    use RefreshDatabase;

    /**
     * @throws BindingResolutionException
     */
    public function testIsButtonPressed()
    {
        $this->initTelegramBotApiMock();

        $chatId = 4323;

        $buttonService = app(TelegramBotButtonService::class);

        $button1 = $buttonService->makeCreateNewExchangeRequestButton();
        $button2 = $buttonService->makeSendExchangeRequestButton();

        $this->initTelegramBotApiMock([
            TelegramBotApi::CALLBACK_QUERY => [
                'message' => [
                    'chat' => [
                        'id' => $chatId,
                    ],
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                'text' => $button1->get('text'),
                                'callback_data' => $button1->get('callback_data')
                            ],
                            [
                                'text' => $button2->get('text'),
                                'callback_data' => $button2->get('callback_data')
                            ],
                        ],
                    ]
                ],
                'data' => $button2->get('callback_data'),
            ],
        ]);

        $buttonService = app(TelegramBotButtonService::class);

        $this->assertTrue($buttonService->isButtonPressed(app(SendExchangeRequestToServerButton::class)));
    }

    public function testIsButtonPressedReturnFalse()
    {
        $this->initTelegramBotApiMock();

        $chatId = 4323;

        $buttonService = app(TelegramBotButtonService::class);

        $button1 = $buttonService->makeCreateNewExchangeRequestButton();
        $button2 = $buttonService->makeSendExchangeRequestButton();

        $this->initTelegramBotApiMock([
            TelegramBotApi::CALLBACK_QUERY => [
                'message' => [
                    'chat' => [
                        'id' => $chatId,
                    ],
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                'text' => $button1->get('text'),
                                'callback_data' => $button1->get('callback_data')
                            ],
                            [
                                'text' => $button2->get('text'),
                                'callback_data' => $button2->get('callback_data')
                            ],
                        ],
                    ]
                ],
                'data' => $button2->get('callback_data'),
            ],
        ]);

        $buttonService = app(TelegramBotButtonService::class);

        $this->assertFalse($buttonService->isButtonPressed(app(CreateNewExchangeRequestButton::class)));
    }

    public function testMakeBackButton()
    {
        $this->initTelegramBotApiMock();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboardButton = Keyboard::inlineButton([
            'text' => 'Back',
            'callback_data' => json_encode(['back_bot_action']),
        ]);

        $this->assertEquals($keyboardButton, $buttonService->makeBackButton());
    }

    public function testMakeNextButton()
    {
        $this->initTelegramBotApiMock();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboardButton = Keyboard::inlineButton([
            'text' => 'Next',
            'callback_data' => json_encode(['next_bot_action']),
        ]);

        $this->assertEquals($keyboardButton, $buttonService->makeNextButton());
    }

    public function testMakeCreateNewExchangeRequestButton()
    {
        $this->initTelegramBotApiMock();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboardButton = Keyboard::inlineButton([
            'text' => 'Start over',
            'callback_data' => json_encode(['create_new_exchange_request_action']),
        ]);

        $this->assertEquals($keyboardButton, $buttonService->makeCreateNewExchangeRequestButton());
    }

    public function testMakeSendExchangeRequestButton()
    {
        $this->initTelegramBotApiMock();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboardButton = Keyboard::inlineButton([
            'text' => 'Send',
            'callback_data' => json_encode(['send_exchange_request_to_server']),
        ]);

        $this->assertEquals($keyboardButton, $buttonService->makeSendExchangeRequestButton());
    }

    public function testMakeCancelExchangeRequestButton()
    {
        $this->initTelegramBotApiMock();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboardButton = Keyboard::inlineButton([
            'text' => 'Cancel',
            'callback_data' => json_encode(['c_r_e_r_a', 'id' => 'test_id']),
        ]);

        $this->assertEquals($keyboardButton, $buttonService->makeCancelExchangeRequestButton('test_id'));
    }

    /**
     * @throws BindingResolutionException
     */
    public function testMakePayExchangeRequestButton()
    {
        $this->initTelegramBotApiMock();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboardButton = Keyboard::inlineButton([
            'text' => 'I paid',
            'callback_data' => json_encode(['pay_remote_exchange_request_action']),
        ]);

        $this->assertEquals($keyboardButton, $buttonService->makePayExchangeRequestButton());
    }
}