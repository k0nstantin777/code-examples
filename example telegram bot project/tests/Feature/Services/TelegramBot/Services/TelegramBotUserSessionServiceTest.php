<?php

namespace Tests\Feature\Services\TelegramBot\Services;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\TelegramBot\DataTransferObjects\TelegramChatDto;
use App\Services\TelegramBot\Services\TelegramBotConfigService;
use App\Services\TelegramBot\Services\TelegramBotUserSessionService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TelegramBotUserSessionServiceTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testCreateSessionSuccessCreated()
    {
        $this->initJsonRpcClientMock();

        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $now = Carbon::parse('2022-10-04 12:00');
        Carbon::setTestNow($now);

        $service = app(TelegramBotUserSessionService::class);

        $exchangerSession = $service->createSession($user);

        $this->assertEquals([
            $user->id,
            1,
            $now->toDateTimeString(),
        ], [
            $exchangerSession->user_id,
            $exchangerSession->exchanger_user_id,
            $exchangerSession->session_updated_at,
        ]);
    }

    public function testCreateSessionRemoteSessionExpiredThrowAuthException()
    {
        $now = Carbon::parse('2022-10-04 12:00');
        Carbon::setTestNow($now);

        $this->initJsonRpcClientMock([
            'external-customer-sessions.show' => [
                'type' => config('services.exchanger.login_source_type'),
                'customer_id' => 1,
                'params' => [
                    'chat_id' => 4323
                ],
                'expired_at' => $now->copy()->subMinute(),
            ]
        ]);

        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $service = app(TelegramBotUserSessionService::class);

        $this->expectException(AuthenticationException::class);

        $service->createSession($user);

        $this->assertDatabaseMissing(ExchangerSession::class, [
            'user_id' => $user->id,
            'exchanger_user_id' => 1,
        ]);
    }

    public function testCreateSessionSuccessTouchedExists()
    {
        $this->initJsonRpcClientMock();

        $chatId = 4323;
        $user = User::factory()->create([
            'telegram_chat_id' => $chatId,
            'name' => 'Test',
            'username' => 'TestUsername',
        ]);

        $now = Carbon::parse('2022-10-04 12:00');
        Carbon::setTestNow($now);

        $exchangerSession = ExchangerSession::factory()->create([
            'user_id' => $user->id,
            'exchanger_user_id' => 1,
            'session_updated_at' => $now->subMinute(),
        ]);

        $service = app(TelegramBotUserSessionService::class);

        $exchangerSessionUpdated = $service->createSession($user);

        $this->assertEquals([
            $user->id,
            1,
            $now->toDateTimeString(),
            $exchangerSession->id,
        ], [
            $exchangerSessionUpdated->user_id,
            $exchangerSessionUpdated->exchanger_user_id,
            $exchangerSessionUpdated->session_updated_at->toDateTimeString(),
            $exchangerSessionUpdated->id,
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws \Throwable
     */
    public function testUpdateSessionUserNotExistExchangerSessionNotExist()
    {
        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock();

        $service = app(TelegramBotUserSessionService::class);

        $now = Carbon::parse('2022-10-04 12:00');
        Carbon::setTestNow($now);

        $service->updateSession(new TelegramChatDto(
            id: 4323,
            first_name: 'Test User',
            username: 'testuser',
            bot: app(TelegramBotConfigService::class)->getDefaultBot(),
        ));

        $this->assertDatabaseHas(User::class, [
            'name' => 'Test User',
            'username' => 'testuser',
            'telegram_chat_id' => 4323,
            'last_active_at' => $now,
        ]);
    }

    /**
     * @throws UnknownProperties
     * @throws \Throwable
     */
    public function testUpdateSessionUserAndExchangerSessionExists()
    {
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

        $service = app(TelegramBotUserSessionService::class);

        $now = Carbon::parse('2022-10-04 12:00');
        Carbon::setTestNow($now);

        $service->updateSession(new TelegramChatDto(
            id: $chatId,
            first_name: 'Test User',
            username: 'testuser',
            bot: app(TelegramBotConfigService::class)->getDefaultBot(),
        ));

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'name' => 'Test User',
            'username' => 'testuser',
            'telegram_chat_id' => $chatId,
            'last_active_at' => $now,
        ]);

        $this->assertDatabaseHas(ExchangerSession::class, [
            'id' => $exchangerSession->id,
            'user_id' => $user->id,
            'exchanger_user_id' => 1,
            'session_updated_at' => $now,
        ]);
    }
}