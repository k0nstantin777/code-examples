<?php

namespace App\Domains\Exchanger\Services;

use App\Domains\Exchanger\DataTransferObjects\ExchangerSessionCreateDto;
use App\Domains\Exchanger\Models\ExchangerSession;
use Illuminate\Support\Facades\DB;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ExchangerSessionWriteService
{
    public function __construct(
        private readonly ExchangerSessionReadService $exchangerSessionReadService,
    ) {
    }

    public function create(ExchangerSessionCreateDto $dto) : ExchangerSession
    {
        try {
            DB::beginTransaction();

            $exchangerSession = ExchangerSession::create([
                'user_id' => $dto->userId,
                'exchanger_user_id' => $dto->exchangerUserId,
                'session_updated_at' => $dto->sessionUpdatedAt,
            ]);

            DB::commit();

            return $exchangerSession;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function update(int $id, ExchangerSessionCreateDto $dto) : ExchangerSession
    {
        try {
            DB::beginTransaction();

            $exchangerSession = $this->exchangerSessionReadService->getById($id);

            $exchangerSession->update([
                'user_id' => $dto->userId,
                'exchanger_user_id' => $dto->exchangerUserId,
                'session_updated_at' => $dto->sessionUpdatedAt,
            ]);

            DB::commit();

            return $exchangerSession->refresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * @throws UnknownProperties
     * @throws \Throwable
     */
    public function touch(ExchangerSession $exchangerSession) : ExchangerSession
    {
        return $this->update(
            $exchangerSession->id,
            new ExchangerSessionCreateDto(
                user_id: $exchangerSession->user_id,
                exchanger_user_id: $exchangerSession->exchanger_user_id,
                session_updated_at: now(),
            )
        );
    }

    public function delete(int $id) : bool
    {
        try {
            DB::beginTransaction();

            $exchangerSession = $this->exchangerSessionReadService->getById($id);

            $result = $exchangerSession->delete();

            DB::commit();

            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}