<?php

namespace Tests\Feature\Services\TelegramBot\Jobs;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Jobs\TrackExchangeRate;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TrackExchangeRateTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws TelegramSDKException
     * @throws ValidationException
     */
    public function testRefreshExchangeRates()
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
        $exchangeRequest->setReceivedSum(5);
        $exchangeRequest->setCommission(2);
        $exchangeRequestService->save($exchangeRequest);

        $job = app(TrackExchangeRate::class, ['user' => $user]);
        $job->handle();

        $updatedExchangeRequest = $exchangeRequestService->getByUserId($user->id);

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $this->assertEquals([
            1,
            10,
            1,
            5,
            'Rates of current exchange has been updated',
            'Exchange rate: *1 BTC* / *100 ETH* 
Network commission: *1% ETH* 
',
            'Given: *1 BTC*
Received: *10 ETH*
'
        ], [
            $updatedExchangeRequest->getGivenSum(),
            $updatedExchangeRequest->getReceivedSum(),
            $updatedExchangeRequest->getCommission(),
            count($messages),
            $messages[1]->text,
            $messages[2]->text,
            $messages[3]->text,
        ]);
    }

    public function testRefreshExchangeRatesWithoutUpdateCalculateSum()
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
        $exchangeRequestService->save($exchangeRequest);

        $job = app(TrackExchangeRate::class, ['user' => $user]);
        $job->handle();

        $updatedExchangeRequest = $exchangeRequestService->getByUserId($user->id);

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $this->assertEquals([
            null,
            null,
            null,
            4,
            'Rates of current exchange has been updated',
            'Exchange rate: *1 BTC* / *100 ETH* 
Network commission: *1% ETH* 
'
        ], [
            $updatedExchangeRequest->getGivenSum(),
            $updatedExchangeRequest->getReceivedSum(),
            $updatedExchangeRequest->getCommission(),
            count($messages),
            $messages[1]->text,
            $messages[2]->text,
        ]);
    }
}
