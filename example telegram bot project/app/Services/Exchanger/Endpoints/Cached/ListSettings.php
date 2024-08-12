<?php

namespace App\Services\Exchanger\Endpoints\Cached;

use App\Services\Exchanger\Endpoints\ListSettings as BaseListSettings;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\JsonRpcClient;
use App\Services\Exchanger\RequestDTOs\ListSettingsRequestDto;
use App\Services\Exchanger\Storages\Settings\SettingsCollectionStorage;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ListSettings extends BaseListSettings
{
    public function __construct(
        JsonRpcClient $jsonRpcClient,
        private readonly SettingsCollectionStorage $settingsCollectionStorage
    ) {
        parent::__construct($jsonRpcClient);
    }

    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : Collection
    {
        /** @var ListSettingsRequestDto $dto */
        [$dto] = $arguments;

        $key = $this->getKey($dto);
        $settings = $this->settingsCollectionStorage->get($key);

        if (!$settings) {
            $settings = parent::execute($dto);

            $this->settingsCollectionStorage->save($key, $settings);
        }

        return $settings;
    }

    private function getKey(ListSettingsRequestDto $dto) : string
    {
        return json_encode($dto->toArray());
    }
}
