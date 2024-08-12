<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitConfirmExchangeRequestState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitEnterEmailOrLoginState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitEnterFormAttributesState;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Datto\JsonRpc\Responses\ErrorResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Keyboard\Keyboard;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class AwaitEnterEmailOrLoginStateFlowTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testEnterEmailMessageCustomerNotExists(): void
    {
        $this->initTelegramBotApiMock();
        $this->initJsonRpcClientMock([
            "customers.show" => new ErrorResponse(1, "Resource not found", -32600)
        ]);

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
        $exchangeRequest->changeState(app(AwaitEnterEmailOrLoginState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 'test@email.com',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitEnterFormAttributesState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
                $expectedState,
                'Please enter next required information: *"Record details of the received currency"* (Ethereum address)',
                'test@email.com'
            ], [
                $exchangeRequest->getState(),
                $messages[0]->text,
                $exchangeRequest->getEmail(),
            ]);
    }

    public function testEnterEmailMessageCustomerAlreadyExists(): void
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
        $exchangeRequest->changeState(app(AwaitEnterEmailOrLoginState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 'test@email.com',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitEnterEmailOrLoginState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            'Customer already exist, please run /login and sign in',
            null,
        ], [
            $exchangeRequest->getState(),
            $messages[0]->text,
            $exchangeRequest->getEmail(),
        ]);
    }

    public function testEnterEmailMessageInvalidEmailAddress(): void
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
        $exchangeRequest->changeState(app(AwaitEnterEmailOrLoginState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 'test',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitEnterEmailOrLoginState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            'Invalid email address',
            null
        ], [
            $exchangeRequest->getState(),
            $messages[0]->text,
            $exchangeRequest->getEmail()
        ]);
    }

    public function testEnterEmailMessageCustomerNotExistsAndFilledAllFormAttributes(): void
    {
        $this->initJsonRpcClientMock(
            [
                "customers.show" => new ErrorResponse(1, "Resource not found", -32600)
            ]
        );
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
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequest->changeState(app(AwaitEnterEmailOrLoginState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 'test@email.com',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitConfirmExchangeRequestState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $buttonService = app(TelegramBotButtonService::class);

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard->row(
            $buttonService->makeCreateNewExchangeRequestButton(),
            $buttonService->makeSendExchangeRequestButton(),
        );

        $this->assertEquals([
            $expectedState,
            'Your current exchange request: 
Given: *1 BTC (Bitcoin)*
Received: *10 ETH (Ethereum)*
Record details of the received currency: *test_requisites*
Phone Number: *12312312312*
',
            'Proceed?',
            $keyboard
        ], [
            $exchangeRequest->getState(),
            $messages[0]->text,
            $messages[1]->text,
            $messages[1]->replyMarkup,
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
        $exchangeRequest->changeState(app(AwaitEnterEmailOrLoginState::class));
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
        $expectedState = app(AwaitEnterEmailOrLoginState::class);
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
