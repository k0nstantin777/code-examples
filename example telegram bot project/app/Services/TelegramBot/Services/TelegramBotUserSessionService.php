<?php

namespace App\Services\TelegramBot\Services;

use App\Domains\Exchanger\DataTransferObjects\ExchangerSessionCreateDto;
use App\Domains\Exchanger\Models\ExchangerSession;
use App\Domains\Exchanger\Services\ExchangerSessionWriteService;
use App\Domains\User\DataTransferObjects\UserCreateDto;
use App\Domains\User\Models\User;
use App\Domains\User\Services\UserReadService;
use App\Domains\User\Services\UserWriteService;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExternalCustomerSessionRequestDto;
use App\Services\Exchanger\Services\ExternalCustomerSessionService;
use App\Services\Language\Enums\LanguageCode;
use App\Services\Language\LanguageService;
use App\Services\TelegramBot\DataTransferObjects\TelegramChatDto;
use App\Services\TelegramBot\Events\ChatLogout;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class TelegramBotUserSessionService
{
    public function __construct(
        private readonly UserWriteService $userWriteService,
        private readonly UserReadService $userReadService,
        private readonly ExchangerSessionWriteService $exchangerSessionWriteService,
        private readonly ExternalCustomerSessionService $externalCustomerSessionService,
        private readonly LanguageService $languageService,
    ) {
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws \Throwable
     */
    public function createSession(User $user) : ExchangerSession
    {
        $externalCustomerSession = $this->externalCustomerSessionService->get(
            new GetExternalCustomerSessionRequestDto(
                type: config('services.exchanger.login_source_type'),
                params: [
                    'name' => $user->telegram_bot_name,
                ]
            )
        );

        if ($externalCustomerSession->isExpired()) {
            throw new AuthenticationException();
        }

        $exchangerSession = $user->exchangerSession;
        $dto = new ExchangerSessionCreateDto(
            user_id: $user->id,
            exchanger_user_id: $externalCustomerSession->customerId,
            session_updated_at: now(),
        );

        if ($exchangerSession) {
            $exchangerSession = $this->exchangerSessionWriteService->update($exchangerSession->id, $dto);
        } else {
            $exchangerSession = $this->exchangerSessionWriteService->create($dto);
        }

        return $exchangerSession;
    }

    /**
     * @throws UnknownProperties
     * @throws \Throwable
     */
    public function updateSession(TelegramChatDto $telegramChatDto) : void
    {
        $user = $this->createOrUpdateUser($telegramChatDto);

        $this->languageService->setAppLanguage(LanguageCode::from($user->lang));
        $this->userWriteService->touchLastActive($user->id);

        $exchangerSession = $this->getOrCreateUserSession($user);

        $this->exchangerSessionWriteService->touch($exchangerSession);
    }

    /**
     * @throws UnknownProperties
     * @throws \Throwable
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    private function getOrCreateUserSession(User $user) : ExchangerSession
    {
        $exchangerSession = $user->exchangerSession;

        if (!$exchangerSession || $exchangerSession->isExpired()) {
            $exchangerSession = $this->createSession($user);
        }

        return $exchangerSession;
    }

    /**
     * @throws UnknownProperties
     * @throws \Throwable
     */
    private function createOrUpdateUser(TelegramChatDto $telegramChatDto) : User
    {
        $user = $this->userReadService->getByBotAndChatOrNull($telegramChatDto->bot->name, $telegramChatDto->id);

        $createUserDto = new UserCreateDto(
            name: $telegramChatDto->firstName,
            username: $telegramChatDto->username,
            telegram_chat_id: $telegramChatDto->id,
            telegram_bot_name: $telegramChatDto->bot->name,
            lang: LanguageCode::EN,
        );

        if (!$user) {
            return $this->userWriteService->create($createUserDto);
        }

        if ($user->name !== $telegramChatDto->firstName || $user->username !== $telegramChatDto->username) {
            $user = $this->userWriteService->update($user->id, $createUserDto);
        }

        return $user;
    }
}
