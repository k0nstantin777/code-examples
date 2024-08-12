<?php

namespace Tests\Feature\Services\TelegramBot\Services;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Services\Exchanger\Enums\ExchangeRequestStatus;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRequestDto;
use App\Services\Exchanger\RequestDTOs\GetExchangeRequestRequestDto;
use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\Exchanger\ValueObjects\Currency;
use App\Services\Exchanger\ValueObjects\Customer;
use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionAccessDetails;
use App\Services\Exchanger\ValueObjects\ExchangeFormAttribute;
use App\Services\TelegramBot\Services\TelegramBotRemoteExchangeDirectionService;
use App\Services\TelegramBot\Services\TelegramBotRemoteExchangeRequestService;
use App\Services\TelegramBot\Storages\ActiveExchangeRequestStorage;
use App\Services\TelegramBot\Storages\ExchangeDirectionStorage;
use App\Services\TelegramBot\Storages\ExchangeRequestStorage;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use App\Domains\User\Models\User;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TelegramBotExchangeRequestServiceTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testGetByUserId()
    {
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
        $exchangeRequest->setRemoteId('c4bed09a-3f3c-495a-b920-b328d574479f');
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);
        $result = $service->getByUserId($user->id);

        $this->assertEquals([
            'c4bed09a-3f3c-495a-b920-b328d574479f',
        ], [
            $result->getRemoteId(),
        ]);
    }

    public function testGetByUserIdReturnNull()
    {
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
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertNull($service->getByUserId(100));
    }

    public function testSave()
    {
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
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertEquals(1, $service->getByUserId($user->id)->getGivenCurrencyId());
    }

    public function testDelete()
    {
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
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);
        $service->delete($exchangeRequest);

        $this->assertEquals(null, $service->getByUserId($user->id));
    }

    public function testGetCustomerEmail()
    {
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
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertEquals([
            'test@email.com',
        ], [
            $service->getCustomerEmail($exchangeRequest),
        ]);
    }

    public function testGetCustomerEmailFromExchanger()
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
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertEquals([
            'test@email.com',
        ], [
            $service->getCustomerEmail($exchangeRequest),
        ]);
    }

    public function testGetCustomerEmailFromExchangerCustomerNotExist()
    {
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
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertEquals([
            '',
        ], [
            $service->getCustomerEmail($exchangeRequest),
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testGetExchangeDirection()
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
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertEquals([
            1,
        ], [
            $service->getExchangeDirection($exchangeRequest)->id,
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testGetExchangeDirectionReturnNull()
    {
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
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertEquals([
            null,
        ], [
            $service->getExchangeDirection($exchangeRequest),
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testGetNextRequiredFormAttribute()
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
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertEquals([
            'requisites_received_currency',
        ], [
            $service->getNextRequiredFormAttribute($exchangeRequest)->code,
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testGetNextRequiredFormAttributeFilledAllAttributes()
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
        $exchangeRequest->setEmail('test@email.com');
        $exchangeRequest->setFilledFormAttributes([
            'requisites_received_currency' => 'test_requisites',
            'customer_phone' => 12312312312
        ]);
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertEquals([
            null
        ], [
            $service->getNextRequiredFormAttribute($exchangeRequest),
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testGet()
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
        $exchangeRequest->setRemoteId('c4bed09a-3f3c-495a-b920-b328d574479f');
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);
        $remoteExchangeRequest = $service->getRemoteExchangeRequest($exchangeRequest);
        $storage = app(ActiveExchangeRequestStorage::class);

        $storageKey = json_encode((new GetExchangeRequestRequestDto([
            'id' => 'c4bed09a-3f3c-495a-b920-b328d574479f',
            'customer_id' => 1,
        ]))->toArray());

        $exchangeRequestInStorage = $storage->get($storageKey);

        $this->assertEquals([
            'c4bed09a-3f3c-495a-b920-b328d574479f',
            'BTC',
        ], [
            $remoteExchangeRequest->id,
            $exchangeRequestInStorage->givenCurrency->code,
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testGetReturnNull()
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
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertNull($service->getRemoteExchangeRequest($exchangeRequest));
    }

    /**
     * @throws UnknownProperties
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testRefreshRemoteExchangeRequest()
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
        $exchangeRequest->setRemoteId('c4bed09a-3f3c-495a-b920-b328d574479f');
        $exchangeRequestService->save($exchangeRequest);

        $storage = app(ActiveExchangeRequestStorage::class);

        $storageKey = json_encode((new GetExchangeRequestRequestDto([
            'id' => 'c4bed09a-3f3c-495a-b920-b328d574479f',
            'customer_id' => 1,
        ]))->toArray());

        $remoteExchangeRequest = new ActiveExchangeRequest(
            id: "c4bed09a-3f3c-495a-b920-b328d574479f",
            formatted_token: "57300299",
            status_string: "Paid",
            status: ExchangeRequestStatus::PAID,
            created_date_string: "08.09.2022, 16:14",
            created_at: Carbon::parse("2022-09-08T13:14:17.000000Z"),
            given_currency_rate: "1",
            given_sum: "100",
            received_sum: "9.4172",
            received_currency_rate: "0.102971",
            is_expired: false,
            expired_at : Carbon::parse('2022-09-08T14:14:17.000000Z'),
            show_link: "http://exchanger.loc/exchange-requests/c4bed09a-3f3c-495a-b920-b328d574479f",
            payment_address: null,
            commission_string: "9 RUB",
            qr_code_img : '',
            is_payable: true,
            is_rejectable: true,
            is_need_card_verify: false,
            comment_for_customer: null,
            given_currency: new Currency(
                id : 1,
                position : 1,
                label : 'crypto',
                label_description : 'Crypto',
                name : 'Bitcoin',
                code :  'BTC',
                icon :  '',
                reserve : 1,
                exchange_prompt : 'Bitcoin address',
            ),
            received_currency: new Currency(
                id : 2,
                position : 2,
                label : 'crypto',
                label_description : 'Crypto',
                name : 'Ethereum',
                code :  'ETH',
                icon :  '',
                reserve : 1,
                exchange_prompt : 'Ethereum address',
            ),
            attributes: [
                new ExchangeFormAttribute(
                    id: 2,
                    code: "requisites_received_currency",
                    name: "Record details of the received currency",
                    value: "3EfFU4HSsuTHeMrKLuMmj5A5ycoiBePZ8n"
                ),
                new ExchangeFormAttribute(
                    id: 6,
                    code: "customer_phone",
                    name: "Phone Number",
                    value: "+79221630150"
                )
            ],
            customer : new Customer(
                id : 1,
                name : 'Test User',
                email : 'test@email.com',
            ),
            credit_cards: [],
            payment_form_data : [
                'address' => 'test_payment_address'
            ],
            received_requisites : '3EfFU4HSsuTHeMrKLuMmj5A5ycoiBePZ8n',
        );

        $storage->save($storageKey, $remoteExchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);
        $remoteExchangeRequest = $service->refreshRemoteExchangeRequest($exchangeRequest);

        $exchangeRequestInStorage = $storage->get($storageKey);

        $this->assertEquals([
            'c4bed09a-3f3c-495a-b920-b328d574479f',
            ExchangeRequestStatus::AWAITING_PAYMENT,
        ], [
            $remoteExchangeRequest->id,
            $exchangeRequestInStorage->status,
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testRefreshReturnNull()
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
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotExchangeRequestService::class);

        $this->assertNull($service->refreshRemoteExchangeRequest($exchangeRequest));
    }
}