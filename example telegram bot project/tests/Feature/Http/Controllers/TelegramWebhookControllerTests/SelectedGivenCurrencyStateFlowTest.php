<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectReceivedCurrencyButton;
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

class SelectedGivenCurrencyStateFlowTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testSelectedReceivedCurrency(): void
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
        $exchangeRequest->changeState(app(SelectedGivenCurrencyState::class));
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
                    "reply_markup" => [
                        "inline_keyboard"=> [
                            [
                                'text' => $button1->get('text'),
                                'callback_data' => $button1->get('callback_data')
                            ],
                        ],
                    ]
                ],
                'data' => $button1->get('callback_data'),
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $row = [
            $buttonService->makeBackButton(),
            $buttonService->makeNextButton(),
        ];

        $keyboard->row(...$row);

        $response->assertStatus(200);
        $expectedState = app(SelectedReceivedCurrencyState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
                $expectedState,
                2,
                1,
                'Selected received currency: *Bitcoin*',
                'Exchange rate: *1 BTC* / *100 ETH* ' . "\n" . 'Network commission: *1% ETH* ' . "\n",
                'Proceed?',
                $keyboard
            ], [
                $exchangeRequest->getState(),
                $exchangeRequest->getReceivedCurrencyId(),
                $exchangeRequest->getExchangeDirectionId(),
                $messages[0]->text,
                $messages[1]->text,
                $messages[2]->text,
                $messages[2]->replyMarkup
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
        $exchangeRequest->changeState(app(SelectedGivenCurrencyState::class));
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
        $expectedState = app(SelectedGivenCurrencyState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            null,
            'I don\'t understand you',
        ], [
            $exchangeRequest->getState(),
            $exchangeRequest->getReceivedCurrencyId(),
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
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->changeState(app(SelectedGivenCurrencyState::class));
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
        $expectedState = app(SelectedGivenCurrencyState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            null,
            'I don\'t understand you',
        ], [
            $exchangeRequest->getState(),
            $exchangeRequest->getReceivedCurrencyId(),
            $messages[0]->text,
        ]);
    }
}
