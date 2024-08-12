<?php

namespace Tests\Feature\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowCurrentExchangeRequestHandler;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class ShowCurrentExchangeRequestHandlerTest extends TestCase
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

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(10);
        $exchangeRequest->setReceivedSum(100);
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequestService->save($exchangeRequest);

        $handler = app(ShowCurrentExchangeRequestHandler::class, ['exchangeRequest' => $exchangeRequest]);
        $handler->handle();

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $this->assertEquals([
            'Your current exchange request: 
Given: *10 BTC (Bitcoin)*
Received: *100 ETH (Ethereum)*
Record details of the received currency: *test_requisites*
Phone Number: *12312312312*
',
            1
        ], [
            $messages[0]->text,
            count($messages)
        ]);
    }

    public function testHandleShortInfo()
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

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(10);
        $exchangeRequest->setReceivedSum(100);
        $exchangeRequestService->save($exchangeRequest);

        $handler = app(ShowCurrentExchangeRequestHandler::class, ['exchangeRequest' => $exchangeRequest]);
        $handler->handle();

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $this->assertEquals([
            'Your current exchange request: 
Given: *10 BTC (Bitcoin)*
Received: *100 ETH (Ethereum)*
',
            1
        ], [
            $messages[0]->text,
            count($messages)
        ]);
    }

    public function testHandleNotFilledExchangeRequest()
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

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequest->setCalculateType(CalculateSumType::GIVEN_CURRENCY);
        $exchangeRequest->setGivenSum(10);

        $exchangeRequestService->save($exchangeRequest);

        $this->expectException(InvalidBotActionException::class);
        $this->expectExceptionMessage('The exchange request not filled yet, follow the bot prompts');

        $handler = app(ShowCurrentExchangeRequestHandler::class, ['exchangeRequest' => $exchangeRequest]);
        $handler->handle();
    }
}
