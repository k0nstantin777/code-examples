<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateAccountGraveRequestDto;
use Illuminate\Validation\ValidationException;

class CreateAccountGrave extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return array
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function execute(...$arguments): array
    {
        /* @var CreateAccountGraveRequestDto $dto */
        [$dto] = $arguments;

        return $this->jsonRpcClient->send('account/graves.store', [
            'user_id' => $dto->userId,
            'cemetery_id' => $dto->cemeteryId,
            'section' => $dto->section,
            'lot' => $dto->lot,
            'space' => $dto->space,
            'building' => $dto->building,
            'tier' => $dto->tier,
            'notes' => $dto->notes,
            'loved_info' => $dto->lovedInfo,
            'contact_phone' => $dto->contactPhone,
        ]);
    }
}
