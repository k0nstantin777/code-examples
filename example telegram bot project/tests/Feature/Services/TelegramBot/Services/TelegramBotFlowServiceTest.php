<?php

namespace Tests\Feature\Services\TelegramBot\Services;

use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectGivenCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitSelectGivenCurrencyState;
use App\Services\TelegramBot\Handlers\ErrorHandler;
use App\Services\TelegramBot\Handlers\InvalidActionHandler;
use App\Services\TelegramBot\Handlers\ValidationErrorHandler;
use App\Services\TelegramBot\Services\TelegramBotFlowService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use App\Domains\User\Models\User;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\BotsManager;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TelegramBotFlowServiceTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;


    public function testUpdateExchangeRequest()
    {
        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);



        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

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

        $service = app(TelegramBotFlowService::class);
        $update = app(BotsManager::class)->bot()->getWebhookUpdate();
        $service->handleRequest($update);

        $this->assertEquals([
            1,
        ], [
            $exchangeRequestService->getByUserId($user->id)->getGivenCurrencyId(),
        ]);
    }

    public function testUpdateExchangeRequestCreateNew()
    {
        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

        $service = app(TelegramBotFlowService::class);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
            ],
        ]);

        $update = app(BotsManager::class)->bot()->getWebhookUpdate();
        $service->handleRequest($update);

        $this->assertEquals([
            AwaitSelectGivenCurrencyState::class,
        ], [
            get_class($exchangeRequestService->getByUserId($user->id)->getState()),
        ]);
    }

    public function testCreateNewExchangeRequest()
    {
        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

        $service = app(TelegramBotFlowService::class);

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
            ],
        ]);

        $update = app(BotsManager::class)->bot()->getWebhookUpdate();
        $service->handleRequest($update);

        $this->assertEquals([
            AwaitSelectGivenCurrencyState::class,
        ], [
            get_class($exchangeRequestService->getByUserId($user->id)->getState()),
        ]);
    }

    public function testGetErrorHandler()
    {
        $this->initTelegramBotApiMock();

        $service = app(TelegramBotFlowService::class);

        $this->assertEquals([
            app(ErrorHandler::class),
        ], [
            $service->getErrorHandler(),
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testGetInvalidActionHandler()
    {
        $this->initTelegramBotApiMock();

        $service = app(TelegramBotFlowService::class);

        $this->assertEquals([
            app(InvalidActionHandler::class, ['message' => 'test']),
        ], [
            $service->getInvalidActionHandler('test'),
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testGetValidationErrorHandler()
    {
        $this->initTelegramBotApiMock();

        $service = app(TelegramBotFlowService::class);

        $errors = [
            'id' => ['Invalid Id'],
            'test' => ['Invalid Test']
        ];

        $this->assertEquals([
            app(ValidationErrorHandler::class, ['errors' => $errors]),
        ], [
            $service->getValidationErrorHandler($errors),
        ]);
    }
}