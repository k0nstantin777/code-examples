<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\ListSettingsRequestDto;
use App\Services\Exchanger\ValueObjects\Setting;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ListSettings extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : Collection
    {
        /** @var ListSettingsRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('settings', array_filter([
            'codes' => $dto->codes,
        ]));

        $result = collect();

        foreach ($response as $settingData) {
            $result->push(new Setting(
                group: $settingData['group'],
                code: $settingData['code'],
                value: $settingData['value'],
            ));
        }

        return $result;
    }
}
