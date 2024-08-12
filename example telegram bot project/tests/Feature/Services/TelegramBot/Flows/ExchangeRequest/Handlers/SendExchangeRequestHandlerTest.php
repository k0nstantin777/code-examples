<?php

namespace Tests\Feature\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\SendExchangeRequestHandler;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Datto\JsonRpc\Responses\ErrorResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class SendExchangeRequestHandlerTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws TelegramSDKException
     * @throws ValidationException
     * @throws \Exception
     */
    public function testHandle()
    {
        $chatId = 4323;

        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 'random text',
            ],
        ]);

        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangerSession = ExchangerSession::factory()->create([
            'user_id' => $user->id,
            'exchanger_user_id' => 1,
        ]);

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(10);
        $exchangeRequest->setReceivedSum(100);
        $exchangeRequest->setCommission(1);
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequestService->save($exchangeRequest);

        $handler = app(SendExchangeRequestHandler::class, ['exchangeRequest' => $exchangeRequest]);
        $handler->handle();

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $this->assertEquals([
            'The application has been created successfully',
            1,
            'c4bed09a-3f3c-495a-b920-b328d574479f',
            [
                'address' => 'test_payment_address'
            ]
        ], [
            $messages[0]->text,
            count($messages),
            $exchangeRequest->getRemoteId(),
            $exchangeRequest->getPaymentFormData(),
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws TelegramSDKException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testHandleValidationFailed()
    {
        $chatId = 4323;

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
        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => 'random text',
            ],
        ]);

        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $exchangerSession = ExchangerSession::factory()->create([
            'user_id' => $user->id,
            'exchanger_user_id' => 1,
        ]);

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(10);
        $exchangeRequest->setReceivedSum(100);
        $exchangeRequest->setCommission(1);
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequestService->save($exchangeRequest);

        $this->expectException(ValidationException::class);

        $handler = app(SendExchangeRequestHandler::class, ['exchangeRequest' => $exchangeRequest]);
        $handler->handle();

        $this->assertEquals([
            null,
            null
        ], [
            $exchangeRequest->getRemoteId(),
            $exchangeRequest->getPaymentFormData(),
        ]);
    }
}
