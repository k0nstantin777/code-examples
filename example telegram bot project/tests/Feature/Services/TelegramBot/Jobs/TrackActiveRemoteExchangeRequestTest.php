<?php

namespace Tests\Feature\Services\TelegramBot\Jobs;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitCustomerActionForExchangeRequestState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\CompleteExchangeRequestState;
use App\Services\TelegramBot\Jobs\TrackActiveRemoteExchangeRequest;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TrackActiveRemoteExchangeRequestTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testRefreshExchangeRequest()
    {
        Queue::fake();

        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

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

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

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
            'address' => 'test_address'
        ]);
        $exchangeRequest->changeState(app(AwaitCustomerActionForExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

        $job = app(TrackActiveRemoteExchangeRequest::class, ['user' => $user]);
        $job->handle();

        $updatedExchangeRequest = $exchangeRequestService->getByUserId($user->id);

        $this->assertEquals([
            'test_payment_address',
            [
                'requisites_received_currency' => '3EfFU4HSsuTHeMrKLuMmj5A5ycoiBePZ8n',
                'customer_phone' => '+79221630150'
            ]
        ], [
            $updatedExchangeRequest->getPaymentFormData()['address'],
            $updatedExchangeRequest->getFilledFormAttributes(),
        ]);
    }

    public function testMarkAsCompletedExchangeRequest()
    {
        Queue::fake();

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
                "is_payable"=> true,
                "is_rejectable"=> true,
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
        $this->initTelegramBotApiMock();

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

        $exchangeRequestService = app(TelegramBotExchangeRequestService::class);

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
            'address' => 'test_address'
        ]);
        $exchangeRequest->changeState(app(AwaitCustomerActionForExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

        $job = app(TrackActiveRemoteExchangeRequest::class, ['user' => $user]);
        $job->handle();

        $updatedExchangeRequest = $exchangeRequestService->getByUserId($user->id);

        $this->assertEquals([
            CompleteExchangeRequestState::class,
        ], [
            get_class($updatedExchangeRequest->getState()),
        ]);
    }
}
