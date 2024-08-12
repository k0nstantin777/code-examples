<?php

namespace App\Services\Exchanger\Services\Settings;

use App\Services\Exchanger\Endpoints\ListSettings;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\ListSettingsRequestDto;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class SettingsService
{
    public function __construct(
        private readonly ListSettings $listSettingsEndpoint
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function getList(ListSettingsRequestDto $dto) : Collection
    {
        return $this->listSettingsEndpoint->execute($dto);
    }
}
