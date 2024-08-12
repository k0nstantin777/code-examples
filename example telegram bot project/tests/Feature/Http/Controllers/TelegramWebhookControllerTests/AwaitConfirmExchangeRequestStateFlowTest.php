<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectGivenCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitConfirmExchangeRequestState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitCustomerActionForExchangeRequestState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitEnterEmailOrLoginState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitSelectGivenCurrencyState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\CreateExchangeRequestValidationFailedState;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use App\Services\TelegramBot\Services\TelegramBotConfigService;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\Services\TelegramBotRequestService;
use App\Services\TelegramBot\ValueObjects\Bot;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Datto\JsonRpc\Responses\ErrorResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Keyboard\Keyboard;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class AwaitConfirmExchangeRequestStateFlowTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testSendExchangeRequestBtnPressedSuccessCreated(): void
    {
        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);
        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangerSession = ExchangerSession::factory()->create([
            'user_id' => $user->id,
            'exchanger_user_id' => 1,
        ]);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(1);
        $exchangeRequest->setReceivedSum(10);
        $exchangeRequest->setCommission(1);
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequest->changeState(app(AwaitConfirmExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

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

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitCustomerActionForExchangeRequestState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $buttonService = app(TelegramBotButtonService::class);

        $keyboard1 = new Keyboard();

        $keyboard1
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard1->row($buttonService->makePayExchangeRequestButton());

        $keyboard2 = new Keyboard();

        $keyboard2
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard2->row($buttonService->makeCancelExchangeRequestButton('c4bed09a-3f3c-495a-b920-b328d574479f'));

        $this->assertEquals([
                $expectedState,
                'c4bed09a-3f3c-495a-b920-b328d574479f',
                [
                    'address' => 'test_payment_address'
                ],
                'The application has been created successfully',
                'Your current exchange request:
Number: *#57300299*
Exchange rate: *1 BTC* / *0.102971 ETH*
Given: *100 BTC (Bitcoin)*
Received: *9.4172 ETH (Ethereum)*
Payment system commission: *9 RUB*
Status: *Awaiting Payment*
Created: *08.09.2022, 16:14*
Expired: *08.09.2022, 14:14*
',
                'To pay follow the next steps
1) Make a *BTC* transfer using the details:
Wallet: *test_payment_address*
On Sum: *100 BTC*
2) You will receive *ETH* on the details:
*3EfFU4HSsuTHeMrKLuMmj5A5ycoiBePZ8n*
3) After payment click the button: *"I paid"*
',
                $keyboard1,
                'For cancel order, click the button *"Cancel"*
',
                $keyboard2,
                4
            ], [
                $exchangeRequest->getState(),
                $exchangeRequest->getRemoteId(),
                $exchangeRequest->getPaymentFormData(),
                $messages[0]->text,
                $messages[1]->text,
                $messages[2]->text,
                $messages[2]->replyMarkup,
                $messages[3]->text,
                $messages[3]->replyMarkup,
                count($messages)
            ]);
    }

    public function testSendExchangeRequestBtnPressedValidationFailed(): void
    {
        $this->initTelegramBotApiMock();
        $this->initJsonRpcClientMock([
            'exchange-requests.store' => new ErrorResponse(
                1,
                'Validation filed',
                -32602,
                [
                    'violations' => [
                        'requisites_received_currency' => [
                            'Invalid format'
                        ]
                    ]
                ]
            )
        ]);

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);
        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangerSession = ExchangerSession::factory()->create([
            'user_id' => $user->id,
            'exchanger_user_id' => 1,
        ]);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(1);
        $exchangeRequest->setReceivedSum(10);
        $exchangeRequest->setCommission(1);
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequest->changeState(app(AwaitConfirmExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

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

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(CreateExchangeRequestValidationFailedState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $buttonService = app(TelegramBotButtonService::class);

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard->row($buttonService->makeCreateNewExchangeRequestButton());

        $this->assertEquals([
            $expectedState,
            null,
            [],
            [
                'requisites_received_currency' => [
                    'Invalid format'
                ]
            ],
            'Errors: 
1) Invalid format
',
            'To create a new exchange, click on the button below',
            $keyboard,
            2
        ], [
            $exchangeRequest->getState(),
            $exchangeRequest->getRemoteId(),
            $exchangeRequest->getPaymentFormData(),
            $exchangeRequest->getCreationValidationErrors(),
            $messages[0]->text,
            $messages[1]->text,
            $messages[1]->replyMarkup,
            count($messages)
        ]);
    }

//    public function testSendExchangeRequestBtnPressedCustomerNotLoggedIn(): void
//    {
//        $this->initJsonRpcClientMock();
//        $this->initTelegramBotApiMock();
//
//        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);
//        $chatId = 4323;
//        $user = User::factory()->create([
//            'telegram_chat_id' => $chatId,
//            'name' => 'Test',
//            'username' => 'TestUsername',
//        ]);
//
//        $exchangeRequest = new ExchangeRequest($user);
//        $exchangeRequest->setGivenCurrencyId(1);
//        $exchangeRequest->setReceivedCurrencyId(2);
//        $exchangeRequest->setExchangeDirectionId(1);
//        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
//        $exchangeRequest->setGivenSum(1);
//        $exchangeRequest->setReceivedSum(10);
//        $exchangeRequest->setCommission(1);
//        $exchangeRequest->setEmail(null);
//        $exchangeRequest->setFilledFormAttributes([
//            'requisites_received_currency' => 'test_requisites',
//            'customer_phone' => 12312312312
//        ]);
//        $exchangeRequest->changeState(app(AwaitConfirmExchangeRequestState::class));
//        $exchangeRequestService->save($exchangeRequest);
//
//        $buttonService = app(TelegramBotButtonService::class);
//
//        $button1 = $buttonService->makeCreateNewExchangeRequestButton();
//        $button2 = $buttonService->makeSendExchangeRequestButton();
//
//        $this->initTelegramBotApiMock([
//            TelegramBotApi::CALLBACK_QUERY => [
//                'message' => [
//                    'chat' => [
//                        'id' => $chatId,
//                    ],
//                    'reply_markup' => [
//                        'inline_keyboard' => [
//                            [
//                                'text' => $button1->get('text'),
//                                'callback_data' => $button1->get('callback_data')
//                            ],
//                            [
//                                'text' => $button2->get('text'),
//                                'callback_data' => $button2->get('callback_data')
//                            ],
//                        ],
//                    ]
//                ],
//                'data' => $button2->get('callback_data'),
//            ],
//        ]);
//
//        $response = $this->post(route('telegram-webhook'));
//
//        $messages = app(BotsManager::class)->bot()->getSentMessages();
//
//        $response->assertStatus(200);
//        $expectedState = app(AwaitEnterEmailOrLoginState::class);
//        $expectedState->setExchangeRequest($exchangeRequest);
//
//        $this->assertEquals([
//            $expectedState,
//            null,
//            [],
//            'Enter your email or run /login command for sign in',
//            1
//        ], [
//            $exchangeRequest->getState(),
//            $exchangeRequest->getRemoteId(),
//            $exchangeRequest->getPaymentFormData(),
//            $messages[0]->text,
//            count($messages)
//        ]);
//    }

    public function testCreateNewExchangeRequestBtnPressed(): void
    {
        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);
        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(1);
        $exchangeRequest->setReceivedSum(10);
        $exchangeRequest->setCommission(1);
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequest->changeState(app(AwaitConfirmExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

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
                'data' => $button1->get('callback_data'),
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitSelectGivenCurrencyState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $button = app(SelectGivenCurrencyButton::class);

        $row = [
            $button->make(
                'Bitcoin',
                ['id' => 1]
            ),
            $button->make(
                'AdvCash USD',
                ['id' => 3]
            ),
        ];

        $keyboard->row(...$row);

        $this->assertEquals([
            $expectedState,
            null,
            [],
            'Please select given currency',
            $keyboard,
            1
        ], [
            $exchangeRequest->getState(),
            $exchangeRequest->getRemoteId(),
            $exchangeRequest->getPaymentFormData(),
            $messages[0]->text,
            $messages[0]->replyMarkup,
            count($messages)
        ]);
    }

    public function testInvalidCallbackQueryReceived(): void
    {
        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);
        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(1);
        $exchangeRequest->setReceivedSum(10);
        $exchangeRequest->setCommission(1);
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequest->changeState(app(AwaitConfirmExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

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
                'data' => 'random_callback_data',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitConfirmExchangeRequestState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            'I don\'t understand you',
        ], [
            $exchangeRequest->getState(),
            $messages[0]->text,
        ]);
    }

    public function testAnyMessageReceivedAsInvalidAction(): void
    {
        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);
        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(1);
        $exchangeRequest->setReceivedSum(10);
        $exchangeRequest->setCommission(1);
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequest->changeState(app(AwaitConfirmExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 'random text',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitConfirmExchangeRequestState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            'I don\'t understand you',
        ], [
            $exchangeRequest->getState(),
            $messages[0]->text,
        ]);
    }
}
