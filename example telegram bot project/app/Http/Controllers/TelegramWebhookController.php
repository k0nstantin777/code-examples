<?php

namespace App\Http\Controllers;

use App\Services\TelegramBot\DataTransferObjects\TelegramChatDto;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\Services\TelegramBotApi;
use App\Services\TelegramBot\Services\TelegramBotFlowService;
use App\Services\TelegramBot\Services\TelegramBotUserSessionService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramWebhookController extends Controller
{
    /**
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function __invoke(
        TelegramBotApi $telegramBotApi,
        TelegramBotFlowService $telegramBotFlowService,
        TelegramBotUserSessionService $telegramBotUserSessionService,
        Request $request
    ) {
        $botFromRequest = $request->getBot();
        $update = $telegramBotApi->getWebhookUpdate();

        Log::debug('Update Given', $update->toArray());

        try {
            $chat = $update->getChat();
            $telegramBotUserSessionService->updateSession(new TelegramChatDto(
                id: $chat->id,
                first_name: $chat->firstName,
                username: $chat->username,
                bot: $botFromRequest,
            ));

            if ($update->hasCommand()) {
                $telegramBotApi->handleCommand();
                return;
            }

            $telegramBotFlowService->handleRequest($update);
        } catch (InvalidBotActionException $exception) {
            $handler = $telegramBotFlowService->getInvalidActionHandler($exception->getMessage());
            $handler->handle();
        } catch (ValidationException $exception) {
            $handler = $telegramBotFlowService->getValidationErrorHandler($exception->errors());
            $handler->handle();
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), ['exception' => $exception]);

            $handler = $telegramBotFlowService->getErrorHandler();
            $handler->handle();
        }
    }
}
