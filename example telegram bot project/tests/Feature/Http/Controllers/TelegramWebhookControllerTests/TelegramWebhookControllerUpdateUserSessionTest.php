<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TelegramWebhookControllerUpdateUserSessionTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testInvokeWithCreateNewUser(): void
    {
        $this->initTelegramBotApiMock();
        $this->initJsonRpcClientMock();

        $now = Carbon::parse('2022-09-27 00:00');

        Carbon::setTestNow($now);

        $response = $this->post(route('telegram-webhook'));

        $response->assertStatus(200);
        $this->assertDatabaseHas(User::class, [
            'name' => 'Test',
            'username' => 'TestUsername',
            'telegram_chat_id' => 468431435,
            'last_active_at' => $now->toDateTimeString(),
        ]);
    }

    public function testInvokeWithTouchUserExchangerSession(): void
    {
        $this->initJsonRpcClientMock();

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

        $this->initTelegramBotApiMock([
            TelegramBotApi::MESSAGE => [
                'chat' => [
                    'id' => 4323
                ]
            ]
        ]);

        $now = Carbon::parse('2022-09-27 00:00');
        Carbon::setTestNow($now);

        $response = $this->post(route('telegram-webhook'));

        $response->assertStatus(200);
        $this->assertDatabaseHas(User::class, [
            'name' => 'Test',
            'username' => 'TestUsername',
            'telegram_chat_id' => 4323,
            'last_active_at' => $now->toDateTimeString(),
        ]);
        $this->assertDatabaseHas(ExchangerSession::class, [
            'user_id' => $user->id,
            'exchanger_user_id' => 1,
            'session_updated_at' => $now->toDateTimeString(),
        ]);
    }
}
