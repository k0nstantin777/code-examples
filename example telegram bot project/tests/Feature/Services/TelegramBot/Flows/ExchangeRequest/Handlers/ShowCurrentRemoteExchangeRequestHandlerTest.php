<?php

namespace Tests\Feature\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowCurrentRemoteExchangeRequestHandler;
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

class ShowCurrentRemoteExchangeRequestHandlerTest extends TestCase
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
        $exchangeRequest->setRemoteId('c4bed09a-3f3c-495a-b920-b328d574479f');
        $exchangeRequestService->save($exchangeRequest);

        $handler = app(ShowCurrentRemoteExchangeRequestHandler::class, ['exchangeRequest' => $exchangeRequest]);
        $handler->handle();

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboard1 = new Keyboard();

        $keyboard1
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard1->row($buttonService->makePayExchangeRequestButton());

        $keyboard2 = new Keyboard();

        $keyboard2
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard2->row($buttonService->makeCancelExchangeRequestButton('c4bed09a-3f3c-495a-b920-b328d574479f'));

        $this->assertEquals([
            'Your current exchange request:
Number: *#57300299*
Exchange rate: *1 BTC* / *0.102971 ETH*
Given: *100 BTC (Bitcoin)*
Received: *9.4172 ETH (Ethereum)*
Payment system commission: *9 RUB*
Status: *Awaiting Payment*
Created: *08.09.2022, 16:14*
Expired: *08.09.2022, 14:14*
',
            'To pay follow the next steps
1) Make a *BTC* transfer using the details:
Wallet: *test_payment_address*
On Sum: *100 BTC*
2) You will receive *ETH* on the details:
*3EfFU4HSsuTHeMrKLuMmj5A5ycoiBePZ8n*
3) After payment click the button: *"I paid"*
',
            $keyboard1,
            'For cancel order, click the button *"Cancel"*
',
            $keyboard2,
            3
        ], [
            $messages[0]->text,
            $messages[1]->text,
            $messages[1]->replyMarkup,
            $messages[2]->text,
            $messages[2]->replyMarkup,
            count($messages)
        ]);
    }

    public function testHandleNotPayableAndRejectableExchangeRequest()
    {
        $chatId = 4323;

        $this->initJsonRpcClientMock([
            'exchange-requests.show' => [
                "id"=> "c4bed09a-3f3c-495a-b920-b328d574479f",
                "formatted_token"=> "57300299",
                "status_string"=> "Suspended",
                "status"=> "suspended",
                "created_date_string"=> "08.09.2022, 16:14",
                "created_at"=> "2022-09-08T13:14:17.000000Z",
                "given_currency_rate"=> "1",
                "given_sum"=> "100",
                "received_sum"=> "9.4172",
                "received_currency_rate"=> "0.102971",
                "is_expired"=> false,
                "expired_at" => '2022-09-08T14:14:17.000000Z',
                "show_link"=> "http://exchanger.loc/exchange-requests/c4bed09a-3f3c-495a-b920-b328d574479f",
                "payment_address"=> null,
                "commission_string"=> "9 RUB",
                "qr_code_img" => '',
                "is_payable"=> false,
                "is_rejectable"=> false,
                "is_need_card_verify"=> false,
                "comment_for_customer"=> null,
                "given_currency" => [
                    'id' => 1,
                    'position' => 1,
                    'label' => 'crypto',
                    'label_description' => 'Crypto',
                    'name' => 'Bitcoin',
                    'code' =>  'BTC',
                    'icon' =>  '',
                    'reserve' => 1,
                    'exchange_prompt' => 'Bitcoin address',
                ],
                "received_currency"=> [
                    'id' => 2,
                    'position' => 2,
                    'label' => 'crypto',
                    'label_description' => 'Crypto',
                    'name' => 'Ethereum',
                    'code' =>  'ETH',
                    'icon' =>  '',
                    'reserve' => 1,
                    'exchange_prompt' => 'Ethereum address',
                ],
                "attributes"=> [
                    [
                        "id"=> 2,
                        "code"=> "requisites_received_currency",
                        "name"=> "Record details of the received currency",
                        "value"=> "3EfFU4HSsuTHeMrKLuMmj5A5ycoiBePZ8n"
                    ],
                    [
                        "id"=> 6,
                        "code"=> "customer_phone",
                        "name"=> "Phone Number",
                        "value"=> "+79221630150"
                    ]
                ],
                "customer" => [
                    'id' => 1,
                    'name' => 'Test User',
                    'email' => 'test@email.com',
                ],
                "credit_cards"=> [
                    [
                        "id"=> 9,
                        "card_number"=> "5522043362842856",
                        "secret_card_number"=> "**** **** **** 2856",
                        "status"=> "rejected",
                        "status_string"=> "Rejected"
                    ],
                ],
                'payment_form_data' => [
                    'address' => 'test_payment_address'
                ],
                'received_requisites' => '3EfFU4HSsuTHeMrKLuMmj5A5ycoiBePZ8n',
            ]
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
        $exchangeRequest->setRemoteId('c4bed09a-3f3c-495a-b920-b328d574479f');
        $exchangeRequestService->save($exchangeRequest);

        $handler = app(ShowCurrentRemoteExchangeRequestHandler::class, ['exchangeRequest' => $exchangeRequest]);
        $handler->handle();

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $buttonService = app(TelegramBotButtonService::class);

        $keyboard1 = new Keyboard();

        $keyboard1
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard1->row($buttonService->makePayExchangeRequestButton());

        $keyboard2 = new Keyboard();

        $keyboard2
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $keyboard2->row($buttonService->makeCancelExchangeRequestButton('c4bed09a-3f3c-495a-b920-b328d574479f'));

        $this->assertEquals([
            'Your current exchange request:
Number: *#57300299*
Exchange rate: *1 BTC* / *0.102971 ETH*
Given: *100 BTC (Bitcoin)*
Received: *9.4172 ETH (Ethereum)*
Payment system commission: *9 RUB*
Status: *Suspended*
Created: *08.09.2022, 16:14*
Expired: *08.09.2022, 14:14*
',
            1
        ], [
            $messages[0]->text,
            count($messages)
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws TelegramSDKException
     * @throws ValidationException
     * @throws \Exception
     */
    public function testHandleInvalidActionWithoutRemoteId()
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
        $exchangeRequestService->save($exchangeRequest);

        $this->expectException(InvalidBotActionException::class);
        $this->expectExceptionMessage('The application does not exist or expired');

        $handler = app(ShowCurrentRemoteExchangeRequestHandler::class, ['exchangeRequest' => $exchangeRequest]);
        $handler->handle();
    }
}
