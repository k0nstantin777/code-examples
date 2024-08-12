<?php

namespace App\Services\TelegramBot\Services;

use App\Services\BaseService;
use App\Services\TelegramBot\Handlers\ErrorHandler;
use App\Services\TelegramBot\Handlers\Handler;
use App\Services\TelegramBot\Handlers\InvalidActionHandler;
use App\Services\TelegramBot\Handlers\ValidationErrorHandler;
use App\Services\TelegramBot\ValueObjects\Chat;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Objects\Update;

class TelegramBotFlowService extends BaseService
{
    public function __construct(
        private readonly TelegramBotApi $telegramBotApi,
        private readonly TelegramBotChatService $telegramBotChatService,
        private readonly TelegramBotHelperService $telegramBotHelperService,
    ) {
    }

    public function handleRequest(Update $update)
    {
        $user = $this->telegramBotHelperService->getUser($update, $this->telegramBotApi->getBot());
        $chat = $this->telegramBotChatService->getByUserId($user->id);

        if (!$chat) {
            $chat = new Chat($user);
        }

        $flow = $chat->getFlow();

        $flow->handleRequest($update);
    }

    public function getErrorHandler() : Handler
    {
        return app(ErrorHandler::class);
    }

    /**
     * @throws BindingResolutionException
     */
    public function getInvalidActionHandler(string $message) : Handler
    {
        return app()->make(InvalidActionHandler::class, ['message' => $message]);
    }

    /**
     * @throws BindingResolutionException
     */
    public function getValidationErrorHandler(array $messages) : Handler
    {
        return app()->make(ValidationErrorHandler::class, ['errors' => $messages]);
    }
}
