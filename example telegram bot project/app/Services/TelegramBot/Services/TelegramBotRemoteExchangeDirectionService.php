<?php

namespace App\Services\TelegramBot\Services;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRequestDto;
use App\Services\Exchanger\Services\ExchangeDirection\ExchangeDirectionService;
use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\TelegramBot\Storages\ExchangeDirectionStorage;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class TelegramBotRemoteExchangeDirectionService
{
    public function __construct(
        private readonly ExchangeDirectionService $exchangeDirectionService,
        private readonly ExchangeDirectionStorage $exchangeDirectionStorage,
    ) {
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return ActiveExchangeRequest|null
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function get(ExchangeRequest $exchangeRequest) : ?ExchangeDirection
    {
        if (!$exchangeRequest->getExchangeDirectionId()) {
            return null;
        }

        $customerId = $exchangeRequest->getUser()->getExchangerUserIdOrNull();

        $requestDto = new GetExchangeDirectionRequestDto(
            id: $exchangeRequest->getExchangeDirectionId(),
            customer_id: $customerId,
        );

        $cacheKey = $this->getCachedKey($requestDto);

        $result = $this->exchangeDirectionStorage->get($cacheKey);

        if (!$result) {
            $result = $this->exchangeDirectionService->get($requestDto);
        }

        if ($result) {
            $this->exchangeDirectionStorage->save($cacheKey, $result);
        }

        return $result;
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function refresh(ExchangeRequest $exchangeRequest) : ?ExchangeDirection
    {
        if (!$exchangeRequest->getExchangeDirectionId()) {
            return null;
        }

        $customerId = $exchangeRequest->getUser()->getExchangerUserIdOrNull();

        $requestDto = new GetExchangeDirectionRequestDto(
            id: $exchangeRequest->getExchangeDirectionId(),
            customer_id: $customerId,
        );

        $cacheKey = $this->getCachedKey($requestDto);

        $this->exchangeDirectionStorage->remove($cacheKey);

        $result = $this->exchangeDirectionService->get($requestDto);

        $this->exchangeDirectionStorage->save($cacheKey, $result);

        return $result;
    }

    private function getCachedKey(DataTransferObject $dto) : string
    {
        return json_encode($dto->toArray());
    }
}
