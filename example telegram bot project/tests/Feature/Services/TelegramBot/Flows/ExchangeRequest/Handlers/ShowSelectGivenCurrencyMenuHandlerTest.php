<?php

namespace Tests\Feature\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\TelegramBot\Flows\ExchangeRequest\Buttons\SelectGivenCurrencyButton;
use App\Services\TelegramBot\Flows\ExchangeRequest\Handlers\ShowSelectGivenCurrencyMenuHandler;
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

class ShowSelectGivenCurrencyMenuHandlerTest extends TestCase
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
        $exchangeRequestService->save($exchangeRequest);

        $handler = app(ShowSelectGivenCurrencyMenuHandler::class);
        $handler->handle();

        $updatedExchangeRequest = $exchangeRequestService->getByUserId($user->id);

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

        $this->assertEquals([
            null,
            null,
            1,
            'Please select given currency',
            $keyboard,
        ], [
            $updatedExchangeRequest->getGivenCurrencyId(),
            $updatedExchangeRequest->getReceivedCurrencyId(),
            count($messages),
            $messages[0]->text,
            $messages[0]->replyMarkup,
        ]);
    }
}
