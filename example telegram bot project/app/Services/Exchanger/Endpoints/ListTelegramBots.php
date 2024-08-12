<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\ListRequestDto;
use App\Services\Exchanger\ValueObjects\MetaData;
use App\Services\Exchanger\ValueObjects\TelegramBotListItem;
use App\Services\Exchanger\ValueObjects\TelegramBotsList;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ListTelegramBots extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : TelegramBotsList
    {
        /** @var ListRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('telegram-bots', array_filter([
            'limit' => $dto->limit,
            'offset' => $dto->offset,
            'sort' => $dto->sort,
            'sort_direction' => $dto->sortDirection,
        ]));

        $items = collect();

        foreach ($response['data'] as $telegramBotData) {
            $items->push(new TelegramBotListItem(
                id: $telegramBotData['id'],
                name: $telegramBotData['name'],
                telegram_name: $telegramBotData['telegram_name'],
                telegram_username: $telegramBotData['telegram_username'],
                telegram_token: $telegramBotData['telegram_token'],
            ));
        }

        return new TelegramBotsList(
            items: $items,
            meta: new MetaData(
                limit: $response['meta']['limit'],
                offset: $response['meta']['offset'],
                total: $response['meta']['total'],
            )
        );
    }
}
