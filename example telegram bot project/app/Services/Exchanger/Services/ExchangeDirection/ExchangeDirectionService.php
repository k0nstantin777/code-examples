<?php

namespace App\Services\Exchanger\Services\ExchangeDirection;

use App\Services\Exchanger\Endpoints\GetExchangeDirection;
use App\Services\Exchanger\Endpoints\ListExchangeDirections;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeDirectionRequestDto;
use App\Services\Exchanger\RequestDTOs\ListExchangeDirectionsRequestDto;
use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionsList;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ExchangeDirectionService
{
    public function __construct(
        protected readonly ListExchangeDirections $listExchangeDirectionsEndpoint,
        protected readonly GetExchangeDirection $getExchangeDirectionEndpoint,
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function getList(ListExchangeDirectionsRequestDto $dto) : ExchangeDirectionsList
    {
        return $this->listExchangeDirectionsEndpoint->execute($dto);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getAll(ListExchangeDirectionsRequestDto $dto) : Collection
    {
        $items = collect();
        $limit = 100;
        $offset = 0;

        while (true) {
            $result = $this->getList(
                new ListExchangeDirectionsRequestDto(
                    limit: $limit,
                    offset: $offset,
                    sort: $dto->sort,
                    sort_direction: $dto->sortDirection,
                    customer_id: $dto->customerId,
                    given_currency_id: $dto->givenCurrencyId,
                    with_inactive: $dto->withInactive,
                    telegram_bot_name: $dto->telegramBotName,
                ),
            );

            $items = $items->concat($result->items);
            $total = $result->meta->total;
            $offset += $limit;

            if ($items->count() === $total) {
                break;
            }
        }

        return $items;
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function get(GetExchangeDirectionRequestDto $dto) : ExchangeDirection
    {
        return $this->getExchangeDirectionEndpoint->execute($dto);
    }
}
