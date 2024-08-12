<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\CancelledExchangeRequestState;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\BotsManager;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class PaidExchangeRequestStateFlowTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testAnyMessageReceivedAsInvalidAction(): void
    {
        $this->initJsonRpcClientMock();

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
        $exchangeRequest->setRemoteId('c4bed09a-3f3c-495a-b920-b328d574479f');
        $exchangeRequest->setPaymentFormData([
            'address' => 'test_payment_address'
        ]);
        $exchangeRequest->changeState(app(CancelledExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 'test_transaction_id'
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(CancelledExchangeRequestState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
                $expectedState,
                'I don\'t understand you',
            ], [
                $exchangeRequest->getState(),
                $messages[0]->text,
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
        $exchangeRequest->setRemoteId('c4bed09a-3f3c-495a-b920-b328d574479f');
        $exchangeRequest->setPaymentFormData([
            'transaction_id' => 'test_transaction_id'
        ]);
        $exchangeRequest->changeState(app(CancelledExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

        $buttonService = app(TelegramBotButtonService::class);

        $button1 = $buttonService->makePayExchangeRequestButton();

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
                'data' => 'random_callback_data',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(CancelledExchangeRequestState::class);
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
