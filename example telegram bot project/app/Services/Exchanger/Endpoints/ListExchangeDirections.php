<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\ListExchangeDirectionsRequestDto;
use App\Services\Exchanger\ValueObjects\Currency;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionListItem;
use App\Services\Exchanger\ValueObjects\ExchangeDirectionsList;
use App\Services\Exchanger\ValueObjects\MetaData;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ListExchangeDirections extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : ExchangeDirectionsList
    {
        /** @var ListExchangeDirectionsRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('exchange-directions', array_filter([
            'customer_id' => $dto->customerId,
            'given_currency_id' => $dto->givenCurrencyId,
            'with_inactive' => $dto->withInactive,
            'limit' => $dto->limit,
            'offset' => $dto->offset,
            'sort' => $dto->sort,
            'sort_direction' => $dto->sortDirection,
            'list_type' => $dto->listType,
            'telegram_bot_name' => $dto->telegramBotName,
        ]));

        $items = collect();

        foreach ($response['data'] as $exchangeDirectionData) {
            $items->push(new ExchangeDirectionListItem(
                id: $exchangeDirectionData['id'],
                given_currency: new Currency($exchangeDirectionData['given_currency']),
                received_currency: new Currency($exchangeDirectionData['received_currency'])
            ));
        }

        return new ExchangeDirectionsList(
            items: $items,
            meta: new MetaData(
                limit: $response['meta']['limit'],
                offset: $response['meta']['offset'],
                total: $response['meta']['total'],
            )
        );
    }
}
