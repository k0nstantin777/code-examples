<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectCalculateSumTypeButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitEnterAmountState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\SelectedExchangeDirectionState;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\BotsManager;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class SelectedExchangeDirectionStateFlowTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testGivenCurrencyCalculateSumTypeBtnPressed(): void
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
        $exchangeRequest->changeState(app(SelectedExchangeDirectionState::class));
        $exchangeRequestService->save($exchangeRequest);

        $button = app(SelectCalculateSumTypeButton::class);

        $button1 = $button->makeForGivenCurrency('BTC');
        $button2 = $button->makeForReceivedCurrency('ETH');

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

        $response->assertStatus(200);
        $expectedState = app(AwaitEnterAmountState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
                $expectedState,
                CalculateSumType::GIVEN_CURRENCY,
                'Enter BTC \(Bitcoin\) amount from 0\.1 BTC',
            ], [
                $exchangeRequest->getState(),
                $exchangeRequest->getCalculateType(),
                $messages[0]->text,
            ]);
    }

    public function testReceivedCurrencyCalculateSumTypeBtnPressed(): void
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
        $exchangeRequest->changeState(app(SelectedExchangeDirectionState::class));
        $exchangeRequestService->save($exchangeRequest);

        $button = app(SelectCalculateSumTypeButton::class);

        $button1 = $button->makeForGivenCurrency('BTC');
        $button2 = $button->makeForReceivedCurrency('ETH');

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

        $response->assertStatus(200);
        $expectedState = app(AwaitEnterAmountState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            CalculateSumType::RECEIVED_CURRENCY,
            'Enter ETH \(Ethereum\) amount up to 1000 ETH',
        ], [
            $exchangeRequest->getState(),
            $exchangeRequest->getCalculateType(),
            $messages[0]->text,
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
        $exchangeRequest->changeState(app(SelectedExchangeDirectionState::class));
        $exchangeRequestService->save($exchangeRequest);

        $button = app(SelectCalculateSumTypeButton::class);

        $button1 = $button->makeForGivenCurrency('BTC');
        $button2 = $button->makeForReceivedCurrency('ETH');

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
                'data' => 'random_data',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(SelectedExchangeDirectionState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            null,
            'I don\'t understand you',
        ], [
            $exchangeRequest->getState(),
            $exchangeRequest->getCalculateType(),
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
        $exchangeRequest->changeState(app(SelectedExchangeDirectionState::class));
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
        $expectedState = app(SelectedExchangeDirectionState::class);
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
