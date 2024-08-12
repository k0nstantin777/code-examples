<?php

namespace Tests\Feature\Services\TelegramBot\Jobs;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\AwaitSelectGivenCurrencyState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\CompleteExchangeRequestState;
use App\Services\TelegramBot\Jobs\TrackActiveRemoteExchangeRequest;
use App\Services\TelegramBot\Jobs\TrackActiveUser;
use App\Services\TelegramBot\Jobs\TrackExchangeRate;
use App\Services\TelegramBot\Services\TelegramBotExchangeRequestService;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TrackActiveUserTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;


    public function testTrackActiveRemoteExchangeRequestDispatched()
    {
        Queue::fake();

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
        $exchangeRequest->setRemoteId('test-remote-id');
        $exchangeRequestService->save($exchangeRequest);

        $job = app(TrackActiveUser::class);
        $job->handle();

        Queue::assertPushed(function (TrackActiveRemoteExchangeRequest $job) use ($user) {
            return $job->user->id === $user->id;
        });
    }

    public function testTrackExchangeRateDispatched()
    {
        Queue::fake();

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
        $exchangeRequest->setExchangeDirectionId(1);
        $exchangeRequestService->save($exchangeRequest);

        $job = app(TrackActiveUser::class);
        $job->handle();

        Queue::assertPushed(function (TrackExchangeRate $job) use ($user) {
            return $job->user->id === $user->id;
        });
        Queue::assertNotPushed(TrackActiveRemoteExchangeRequest::class);
    }

    public function testNothingDispatchedIfExchangeRequestCompleted()
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
        $exchangeRequest->setRemoteId('c4bed09a-3f3c-495a-b920-b328d574479f');
        $exchangeRequest->changeState(app(CompleteExchangeRequestState::class));
        $exchangeRequestService->save($exchangeRequest);

        $job = app(TrackActiveUser::class);
        $job->handle();

        Queue::assertNothingPushed();
    }

    public function testNothingDispatchedIfNotExistAppropriateCases()
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
        $exchangeRequest->changeState(app(AwaitSelectGivenCurrencyState::class));
        $exchangeRequestService->save($exchangeRequest);

        $job = app(TrackActiveUser::class);
        $job->handle();

        Queue::assertNothingPushed();
    }
}
