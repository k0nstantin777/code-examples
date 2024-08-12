<?php

namespace App\Services\Exchanger\Services\ExchangeRequest;

use App\Services\Exchanger\Endpoints\CreateExchangeRequest;
use App\Services\Exchanger\Endpoints\GetActiveExchangeRequest;
use App\Services\Exchanger\Endpoints\PayExchangeRequest;
use App\Services\Exchanger\Endpoints\RejectExchangeRequest;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\CreateExchangeRequestRequestDto;
use App\Services\Exchanger\RequestDTOs\GetExchangeRequestRequestDto;
use App\Services\Exchanger\RequestDTOs\PayExchangeRequestRequestDto;
use App\Services\Exchanger\RequestDTOs\RejectExchangeRequestRequestDto;
use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ExchangeRequestService
{
    public function __construct(
        private readonly CreateExchangeRequest $createExchangeRequestEndpoint,
        private readonly GetActiveExchangeRequest $getActiveExchangeRequestEndpoint,
        private readonly PayExchangeRequest $payExchangeRequestEndpoint,
        private readonly RejectExchangeRequest $rejectExchangeRequestEndpoint,
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function create(CreateExchangeRequestRequestDto $dto) : string
    {
        return $this->createExchangeRequestEndpoint->execute($dto);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getActive(GetExchangeRequestRequestDto $dto) : ActiveExchangeRequest
    {
        return $this->getActiveExchangeRequestEndpoint->execute($dto);
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function pay(PayExchangeRequestRequestDto $dto) : bool
    {
        return $this->payExchangeRequestEndpoint->execute($dto);
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function reject(RejectExchangeRequestRequestDto $dto) : bool
    {
        return $this->rejectExchangeRequestEndpoint->execute($dto);
    }
}
