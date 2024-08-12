<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectCalculateSumTypeButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectReceivedCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\SelectedExchangeDirectionState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\SelectedGivenCurrencyState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\SelectedReceivedCurrencyState;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Keyboard\Keyboard;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class SelectedReceivedCurrencyStateFlowTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testNextBtnPressed(): void
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
        $exchangeRequest->changeState(app(SelectedReceivedCurrencyState::class));
        $exchangeRequestService->save($exchangeRequest);

        $buttonService = app(TelegramBotButtonService::class);

        $button1 = $buttonService->makeBackButton();
        $button2 = $buttonService->makeNextButton();

        $this->initTelegramBotApiMock([
            TelegramBotApi::CALLBACK_QUERY => [
                'message' => [
                    'chat' => [
                        'id' => $chatId,
                    ],
                    "reply_markup" => [
                        "inline_keyboard"=> [
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

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $button = app(SelectCalculateSumTypeButton::class);

        $row = [
            $button->makeForGivenCurrency('BTC'),
            $button->makeForReceivedCurrency('ETH'),
        ];

        $keyboard->row(...$row);

        $response->assertStatus(200);
        $expectedState = app(SelectedExchangeDirectionState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
                $expectedState,
                'Please select currency for calculating exchange sum',
                $keyboard
            ], [
                $exchangeRequest->getState(),
                $messages[0]->text,
                $messages[0]->replyMarkup
            ]);
    }

    public function testBackBtnPressed(): void
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
        $exchangeRequest->changeState(app(SelectedReceivedCurrencyState::class));
        $exchangeRequestService->save($exchangeRequest);

        $buttonService = app(TelegramBotButtonService::class);

        $button1 = $buttonService->makeBackButton();
        $button2 = $buttonService->makeNextButton();

        $this->initTelegramBotApiMock([
            TelegramBotApi::CALLBACK_QUERY => [
                'message' => [
                    'chat' => [
                        'id' => $chatId,
                    ],
                    "reply_markup" => [
                        "inline_keyboard"=> [
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

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $button = app(SelectReceivedCurrencyButton::class);

        $row = [
            $button->make(
                'Ethereum',
                ['id' => 2, 'e_id' => 1]
            ),
        ];

        $keyboard->row(...$row);

        $response->assertStatus(200);
        $expectedState = app(SelectedGivenCurrencyState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            'Please select received currency',
            $keyboard
        ], [
            $exchangeRequest->getState(),
            $messages[0]->text,
            $messages[0]->replyMarkup
        ]);
    }

    public function testInvalidCallbackQueryReceived(): void
    {
        $this->initTelegramBotApiMock();
        $this->initJsonRpcClientMock();

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
        $exchangeRequest->changeState(app(SelectedReceivedCurrencyState::class));
        $exchangeRequestService->save($exchangeRequest);

        $button = app(SelectReceivedCurrencyButton::class);

        $button1 = $button->make(
            'Ethereum',
            ['id' => 2, 'e_id' => 1]
        );

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
                        ],
                    ]
                ],
                'data' => 'random_data',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(SelectedReceivedCurrencyState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            'I don\'t understand you',
        ], [
            $exchangeRequest->getState(),
            $messages[0]->text,
        ]);
    }

    public function testMessageReceivedAnswerInvalidAction(): void
    {
        $this->initTelegramBotApiMock();
        $this->initJsonRpcClientMock();

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
        $exchangeRequest->changeState(app(SelectedReceivedCurrencyState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(SelectedReceivedCurrencyState::class);
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
