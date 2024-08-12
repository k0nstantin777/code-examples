<?php

namespace App\Services\Exchanger\Services\ExchangeDirection;

use App\Services\Exchanger\Endpoints\GetExchangeDirection;
use App\Services\Exchanger\Endpoints\ListExchangeDirections;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRequestDto;
use App\Services\Exchanger\RequestDTOs\ListExchangeDirectionsRequestDto;
use App\Services\Exchanger\Storages\ExchangeDirection\ExchangeDirectionStorage;
use App\Services\Exchanger\Storages\ExchangeDirection\ExchangeDirectionCollectionCacheStorage;
use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ExchangeDirectionCachedService extends ExchangeDirectionService
{
    public function __construct(
        ListExchangeDirections $listExchangeDirectionsEndpoint,
        GetExchangeDirection $getExchangeDirectionEndpoint,
        private readonly ExchangeDirectionCollectionCacheStorage $exchangeDirectionCollectionCacheStorage,
        private readonly ExchangeDirectionStorage $exchangeDirectionStorage,
    ) {
        parent::__construct($listExchangeDirectionsEndpoint, $getExchangeDirectionEndpoint);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getAll(ListExchangeDirectionsRequestDto $dto) : Collection
    {
        $key = $this->getCacheKeyByDto($dto);
        $result = $this->exchangeDirectionCollectionCacheStorage->get($key);

        if ($result->isEmpty()) {
            $result = parent::getAll($dto);
            $this->exchangeDirectionCollectionCacheStorage->save($key, $result);
        }

        return $result;
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function get(GetExchangeDirectionRequestDto $dto) : ExchangeDirection
    {
        $key = $this->getCacheKeyByDto($dto);
        $result = $this->exchangeDirectionStorage->get($key);

        if (!$result) {
            $result = parent::get($dto);
            $this->exchangeDirectionStorage->save($key, $result);
        }

        return $result;
    }

    private function getCacheKeyByDto(DataTransferObject $dataTransferObject) : string
    {
        return json_encode($dataTransferObject->toArray());
    }
}
