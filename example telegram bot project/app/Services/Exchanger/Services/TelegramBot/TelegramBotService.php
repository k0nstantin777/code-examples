<?php

namespace App\Services\Exchanger\Services\TelegramBot;

use App\Services\Exchanger\Endpoints\GetTelegramBot;
use App\Services\Exchanger\Endpoints\ListTelegramBots;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetTelegramBotRequestDto;
use App\Services\Exchanger\RequestDTOs\ListRequestDto;
use App\Services\Exchanger\ValueObjects\TelegramBot;
use App\Services\Exchanger\ValueObjects\TelegramBotsList;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class TelegramBotService
{
    public function __construct(
        protected readonly ListTelegramBots $listTelegramBotsEndpoint,
        protected readonly GetTelegramBot $getTelegramBotEndpoint,
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function getList(ListRequestDto $dto) : TelegramBotsList
    {
        return $this->listTelegramBotsEndpoint->execute($dto);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getAll(ListRequestDto $dto) : Collection
    {
        $items = collect();
        $limit = 100;
        $offset = 0;

        while (true) {
            $result = $this->getList(
                new ListRequestDto(
                    limit: $limit,
                    offset: $offset,
                    sort: $dto->sort,
                    sort_direction: $dto->sortDirection,
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
    public function get(GetTelegramBotRequestDto $dto) : TelegramBot
    {
        return $this->getTelegramBotEndpoint->execute($dto);
    }
}
