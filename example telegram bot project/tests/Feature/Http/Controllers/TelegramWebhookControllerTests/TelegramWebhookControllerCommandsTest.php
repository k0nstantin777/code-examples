<?php

namespace Tests\Feature\Http\Controllers\TelegramWebhookControllerTests;

use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\User\Models\User;
use App\Services\TelegramBot\Events\ChatLogout;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectGivenCurrencyButton;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Keyboard\Keyboard;
use Tests\Mocks\Exchanger\JsonRpcClientMock;
use Tests\Mocks\TelegramBot\TelegramBotApi;
use Tests\Mocks\TelegramBot\TelegramBotApiMock;
use Tests\TestCase;

class TelegramWebhookControllerCommandsTest extends TestCase
{
    use TelegramBotApiMock;
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testInvokeCommandStartExchangeReceived(): void
    {
        $this->initJsonRpcClientMock();
        $this->initTelegramBotApiMock([
            TelegramBotApi::COMMAND => [],
        ]);

        $response = $this->post(route('telegram-webhook'));

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $button = app(SelectGivenCurrencyButton::class);

        $row = [
            $button->make(
                'Bitcoin',
                ['id' => 1]
            ),
            $button->make(
                'AdvCash USD',
                ['id' => 3]
            ),
        ];

        $keyboard->row(...$row);

        $response->assertStatus(200);

        $this->assertEquals([
                'Please select given currency',
                $keyboard
            ], [
                $messages[0]->text,
                $messages[0]->replyMarkup
            ]);
    }

//    public function testInvokeCommandLoginReceivedWhenUserNotLoggedIn(): void
//    {
//        $this->initTelegramBotApiMock([
//            TelegramBotApi::COMMAND => [
//                "text" => "/login",
//            ],
//        ]);
//
//        $response = $this->post(route('telegram-webhook'));
//
//        $messages = app(BotsManager::class)->bot()->getSentMessages();
//
//        $response->assertStatus(200);
//
//        $shortUrl = ShortURL::latest()->first();
//
//        $text = 'Goto [link](?t=etb&id=468431435&redirect_url=' . $shortUrl->default_short_url . ') and login';
//
//        $this->assertEquals([
//                $text,
//            ], [
//                $messages[0]->text,
//            ]);
//    }
//
//    public function testInvokeCommandLoginReceivedWhenUserAlreadyLoggedIn(): void
//    {
//        $this->initJsonRpcClientMock();
//
//        $chatId = 4323;
//        $user = User::factory()->create([
//            'telegram_chat_id' => $chatId,
//            'name' => 'Test',
//            'username' => 'TestUsername',
//        ]);
//        $exchangerSession = ExchangerSession::factory()->create([
//            'user_id' => $user->id,
//            'exchanger_user_id' => 1,
//        ]);
//
//        $this->initTelegramBotApiMock([
//            TelegramBotApi::COMMAND => [
//                'chat' => [
//                    'id' => 4323
//                ],
//                "text" => "/login",
//            ],
//        ]);
//
//        $response = $this->post(route('telegram-webhook'));
//
//        $messages = app(BotsManager::class)->bot()->getSentMessages();
//
//        $response->assertStatus(200);
//
//        $text = 'You are already login as *Test User*';
//
//        $this->assertEquals([
//            $text,
//        ], [
//                $messages[0]->text,
//            ]);
//    }
//
//    public function testInvokeCommandLogoutReceivedWhenUserNotLoggedInYet(): void
//    {
//        $this->initTelegramBotApiMock([
//            TelegramBotApi::COMMAND => [
//                "text" => "/logout",
//            ],
//        ]);
//
//        $response = $this->post(route('telegram-webhook'));
//
//        $messages = app(BotsManager::class)->bot()->getSentMessages();
//
//        $response->assertStatus(200);
//
//        $this->assertEquals([
//            'You are not login yet',
//        ], [
//                $messages[0]->text,
//            ]);
//    }
//
//    public function testInvokeCommandLogoutReceivedWhenUserLoggedIn(): void
//    {
//        Event::fake();
//
//        $chatId = 4323;
//        $user = User::factory()->create([
//            'telegram_chat_id' => $chatId,
//            'name' => 'Test',
//            'username' => 'TestUsername',
//        ]);
//        $exchangerSession = ExchangerSession::factory()->create([
//            'user_id' => $user->id,
//            'exchanger_user_id' => 1,
//        ]);
//
//        $this->initTelegramBotApiMock([
//            TelegramBotApi::COMMAND => [
//                'chat' => [
//                    'id' => 4323
//                ],
//                "text" => "/logout",
//            ],
//        ]);
//
//        $response = $this->post(route('telegram-webhook'));
//
//        $messages = app(BotsManager::class)->bot()->getSentMessages();
//
//        $response->assertStatus(200);
//
//        Event::assertDispatched(function (ChatLogout $event) use ($user) {
//            return $event->user->id === $user->id;
//        });
//
//        $this->assertEquals([
//            'Logout successfully',
//        ], [
//                $messages[0]->text,
//            ]);
//
//        $this->assertDatabaseMissing(ExchangerSession::class, [
//            'user_id' => $user->id,
//        ]);
//    }
}
