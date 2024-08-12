<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectGivenCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectReceivedCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitSelectGivenCurrencyState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\SelectedGivenCurrencyState;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Keyboard\Keyboard;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class AwaitSelectGivenCurrencyStateFlowTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testSelectedGivenCurrency(): void
    {
        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->changeState(app(AwaitSelectGivenCurrencyState::class));
        $exchangeRequestService->save($exchangeRequest);

        $button = app(SelectGivenCurrencyButton::class);

        $button1 = $button->make(
            'Bitcoin',
            ['id' => 1]
        );
        $button2 = $button->make(
            'AdvCash USD',
            ['id' => 3]
        );

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
                            ]
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
                1,
                'Selected given currency: *Bitcoin*',
                'Please select received currency',
                $keyboard
            ], [
                $exchangeRequest->getState(),
                $exchangeRequest->getGivenCurrencyId(),
                $messages[0]->text,
                $messages[1]->text,
                $messages[1]->replyMarkup
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
        $exchangeRequest->changeState(app(AwaitSelectGivenCurrencyState::class));
        $exchangeRequestService->save($exchangeRequest);

        $button = app(SelectGivenCurrencyButton::class);

        $button1 = $button->make(
            'Bitcoin',
            ['id' => 1]
        );
        $button2 = $button->make(
            'AdvCash USD',
            ['id' => 3]
        );

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
                            ]
                        ],
                    ]
                ],
                'data' => 'random_data',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitSelectGivenCurrencyState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            null,
            'I don\'t understand you',
        ], [
            $exchangeRequest->getState(),
            $exchangeRequest->getGivenCurrencyId(),
            $messages[0]->text,
        ]);
    }

    public function testMessageReceivedAnswerInvalidAction(): void
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
        $exchangeRequest->changeState(app(AwaitSelectGivenCurrencyState::class));
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
        $expectedState = app(AwaitSelectGivenCurrencyState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            null,
            'I don\'t understand you',
        ], [
            $exchangeRequest->getState(),
            $exchangeRequest->getGivenCurrencyId(),
            $messages[0]->text,
        ]);
    }
}
