<?php

namespace Tests\Feature\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectReceivedCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowSelectReceivedCurrencyMenuHandler;
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

class ShowSelectReceivedCurrencyMenuHandlerTest extends TestCase
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
        $exchangeRequestService->save($exchangeRequest);

        $handler = app(ShowSelectReceivedCurrencyMenuHandler::class);
        $handler->handle();

        $messages = app(BotsManager::class)->bot()->getSentMessages();

        $keyboard = new Keyboard();

        $keyboard
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $button = app(SelectReceivedCurrencyButton::class);

        $row = [
            $button->make(
                'Ethereum',
                ['id' => 2, 'e_id' => 1]
            ),
        ];

        $keyboard->row(...$row);

        $this->assertEquals([
            1,
            'Please select received currency',
            $keyboard
        ], [
            count($messages),
            $messages[0]->text,
            $messages[0]->replyMarkup
        ]);
    }
}
