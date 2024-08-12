<?php

namespace App\Services\TelegramBot\Services;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeRequestRequestDto;
use App\Services\Exchanger\Services\ExchangeRequest\ExchangeRequestService;
use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\TelegramBot\Storages\ActiveExchangeRequestStorage;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class TelegramBotRemoteExchangeRequestService
{
    public function __construct(
        private readonly ExchangeRequestService $exchangeRequestService,
        private readonly ActiveExchangeRequestStorage $activeExchangeRequestStorage,
    ) {
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @return ActiveExchangeRequest|null
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function get(ExchangeRequest $exchangeRequest) : ?ActiveExchangeRequest
    {
        $customerId = $exchangeRequest->getUser()->getExchangerUserIdOrNull();

        if (!$exchangeRequest->getRemoteId() || !$customerId) {
            return null;
        }

        $requestDto = new GetExchangeRequestRequestDto(
            id: $exchangeRequest->getRemoteId(),
            customer_id: $customerId,
        );

        $cacheKey = $this->getCachedKey($requestDto);

        $result = $this->activeExchangeRequestStorage->get($cacheKey);

        if (!$result) {
            $result = $this->exchangeRequestService->getActive($requestDto);
        }

        if ($result) {
            $this->activeExchangeRequestStorage->save($cacheKey, $result);
        }

        return $result;
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function refresh(ExchangeRequest $exchangeRequest) : ?ActiveExchangeRequest
    {
        $customerId = $exchangeRequest->getUser()->getExchangerUserIdOrNull();

        if (!$exchangeRequest->getRemoteId() || !$customerId) {
            return null;
        }

        $requestDto = new GetExchangeRequestRequestDto(
            id: $exchangeRequest->getRemoteId(),
            customer_id: $customerId,
        );

        $cacheKey = $this->getCachedKey($requestDto);

        $this->activeExchangeRequestStorage->remove($cacheKey);

        $result = $this->exchangeRequestService->getActive($requestDto);

        $this->activeExchangeRequestStorage->save($cacheKey, $result);

        return $result;
    }

    private function getCachedKey(DataTransferObject $dto) : string
    {
        return json_encode($dto->toArray());
    }
}
