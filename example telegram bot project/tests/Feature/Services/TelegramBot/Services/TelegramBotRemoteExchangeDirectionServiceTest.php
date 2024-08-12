<?php

namespace Tests\Feature\Services\TelegramBot\Services;

use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRequestDto;
use App\Services\Exchanger\ValueObjects\Currency;
use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionAccessDetails;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\Services\TelegramBotRemoteExchangeDirectionService;
use App\Services\TelegramBot\Storages\ExchangeDirectionStorage;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TelegramBotRemoteExchangeDirectionServiceTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

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

        $exchangeRequest = new ExchangeRequest($user);
        $exchangeRequest->setGivenCurrencyId(1);
        $exchangeRequest->setReceivedCurrencyId(2);
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequestService->save($exchangeRequest);

        $service = app(TelegramBotRemoteExchangeDirectionService::class);
        $exchangeDirection = $service->get($exchangeRequest);
        $storage = app(ExchangeDirectionStorage::class);

        $storageKey = json_encode((new GetExchangeDirectionRequestDto([
            'id' => 1,
        ]))->toArray());
        $exchangeDirectionInStorage = $storage->get($storageKey);

        $this->assertEquals([
            1,
            'BTC',
        ], [
            $exchangeDirection->id,
            $exchangeDirectionInStorage->givenCurrency->code,
        ]);
    }

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

        $service = app(TelegramBotRemoteExchangeDirectionService::class);

        $this->assertNull($service->get($exchangeRequest));
    }

    /**
     * @throws UnknownProperties
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function testRefresh()
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
        $exchangeRequestService->save($exchangeRequest);

        $storage = app(ExchangeDirectionStorage::class);

        $storageKey = json_encode((new GetExchangeDirectionRequestDto([
            'id' => 1,
        ]))->toArray());

        $exchangeDirection = new ExchangeDirection(
            id: 1,
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
            given_currency_rate: 1,
            received_currency_rate: 105,
            given_min_sum: 0,
            given_max_sum: 0,
            received_min_sum: 0,
            received_max_sum: 0,
            commission_value: 1,
            access: new ExchangeDirectionAccessDetails(
                is_allowed : true,
                cause :''
            ),
            form_attributes: [],
        );

        $storage->save($storageKey, $exchangeDirection);

        $service = app(TelegramBotRemoteExchangeDirectionService::class);
        $exchangeDirection = $service->refresh($exchangeRequest);

        $exchangeDirectionInStorage = $storage->get($storageKey);

        $this->assertEquals([
            1,
            100,
        ], [
            $exchangeDirection->id,
            $exchangeDirectionInStorage->receivedCurrencyRate,
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

        $service = app(TelegramBotRemoteExchangeDirectionService::class);

        $this->assertNull($service->refresh($exchangeRequest));
    }
}