<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitEnterAmountState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\ExchangeSumCalculatedState;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class AwaitEnterAmountStateFlowTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    /**
     * @throws TelegramSDKException
     * @throws BindingResolutionException
     */
    public function testEnteredGivenAmount(): void
    {
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
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->changeState(app(AwaitEnterAmountState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 1
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(ExchangeSumCalculatedState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

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

        $this->assertEquals([
                $expectedState,
                1,
                10,
                1,
                'Given: *1 BTC*' . "\n" . 'Received: *10 ETH*' . "\n" ,
                'Proceed?',
                $keyboard,
            ], [
                $exchangeRequest->getState(),
                $exchangeRequest->getGivenSum(),
                $exchangeRequest->getReceivedSum(),
                $exchangeRequest->getCommission(),
                $messages[0]->text,
                $messages[1]->text,
                $messages[1]->replyMarkup,
            ]);
    }

    public function testEnteredReceivedAmount(): void
    {
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
        $exchangeRequest->setCalculateType(CalculateSumType::RECEIVED_CURRENCY);
        $exchangeRequest->changeState(app(AwaitEnterAmountState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 10
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(ExchangeSumCalculatedState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

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

        $this->assertEquals([
            $expectedState,
            1,
            10,
            1,
            'Given: *1 BTC*' . "\n" . 'Received: *10 ETH*' . "\n" ,
            'Proceed?',
            $keyboard,
        ], [
            $exchangeRequest->getState(),
            $exchangeRequest->getGivenSum(),
            $exchangeRequest->getReceivedSum(),
            $exchangeRequest->getCommission(),
            $messages[0]->text,
            $messages[1]->text,
            $messages[1]->replyMarkup,
        ]);
    }

    public function testInvalidCallbackQueryReceived(): void
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
        $exchangeRequest->setCalculateType(CalculateSumType::RECEIVED_CURRENCY);
        $exchangeRequest->changeState(app(AwaitEnterAmountState::class));
        $exchangeRequestService->save($exchangeRequest);

        $this->initTelegramBotApiMock([
            TelegramBotApi::CALLBACK_QUERY => [
                'message' => [
                    'chat' => [
                        'id' => $chatId,
                    ],
                ],
                'data' => 'random_data',
            ],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $response->assertStatus(200);
        $expectedState = app(AwaitEnterAmountState::class);
        $expectedState->setExchangeRequest($exchangeRequest);

        $this->assertEquals([
            $expectedState,
            'I don\'t understand you',
            null,
            null,
            null,
        ], [
            $exchangeRequest->getState(),
            $messages[0]->text,
            $exchangeRequest->getGivenSum(),
            $exchangeRequest->getReceivedSum(),
            $exchangeRequest->getCommission(),
        ]);
    }
}
