<?php

namespace Tests\Feature\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\SendRequestExchangeValidationFiledHandler;
use App\Services\TelegramBot\Services\TelegramBotButtonService;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class SendRequestExchangeValidationFiledHandlerTest extends TestCase
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
        $exchangeRequest->setCreationValidationErrors([
            'id' => [
                'Id is Required'
            ],
            'test_attribute' => [
                'test error message'
            ]
        ]);
        $exchangeRequestService->save($exchangeRequest);

        $handler = app(SendRequestExchangeValidationFiledHandler::class, ['exchangeRequest' => $exchangeRequest]);
        $handler->handle();

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard->row($buttonService->makeCreateNewExchangeRequestButton());

        $this->assertEquals([
            'Errors: 
1) Id is Required
2) test error message
',
            'To create a new exchange, click on the button below',
            $keyboard,
            2
        ], [
            $messages[0]->text,
            $messages[1]->text,
            $messages[1]->replyMarkup,
            count($messages)
        ]);
    }
}
