<?php

namespace App\Domains\User\Services;

use App\Domains\User\DataTransferObjects\UserCreateDto;
use App\Domains\User\Models\User;
use App\Services\Language\Enums\LanguageCode;
use Illuminate\Support\Facades\DB;

class UserWriteService
{
    public function __construct(
        private readonly UserReadService $userReadService,
    ) {
    }

    public function create(UserCreateDto $dto) : User
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'telegram_chat_id' => $dto->telegramChatId,
                'telegram_bot_name' => $dto->telegramBotName,
                'name' => $dto->name,
                'username' => $dto->username,
                'lang' => $dto->lang->value,
            ]);

            DB::commit();

            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function update(int $id, UserCreateDto $dto) : User
    {
        try {
            DB::beginTransaction();

            $user = $this->userReadService->getById($id);

            $user->update([
                'telegram_chat_id' => $dto->telegramChatId,
                'telegram_bot_name' => $dto->telegramBotName,
                'name' => $dto->name,
                'username' => $dto->username,
                'lang' => $dto->lang->value,
            ]);

            DB::commit();

            return $user->refresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function touchLastActive(int $id) : User
    {
        try {
            DB::beginTransaction();

            $user = $this->userReadService->getById($id);

            $user->update([
                'last_active_at' => now(),
            ]);

            DB::commit();

            return $user->refresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function setLanguage(int $id, LanguageCode $languageCode) : User
    {
        try {
            DB::beginTransaction();

            $user = $this->userReadService->getById($id);

            $user->update([
                'lang' => $languageCode->value,
            ]);

            DB::commit();

            return $user->refresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
