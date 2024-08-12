<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetTelegramBotRequestDto;
use App\Services\Exchanger\ValueObjects\TelegramBot;
use App\Services\Exchanger\ValueObjects\TelegramBotAttribute;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class GetTelegramBot extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : TelegramBot
    {
        /** @var GetTelegramBotRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('telegram-bots.show', [
            'id' => $dto->id,
        ]);

        $attributes = [];

        foreach ($response['attributes'] as $attributeData) {
            $attributes[] = new TelegramBotAttribute($attributeData);
        }

        return new TelegramBot(
            id: $response['id'],
            name: $response['name'],
            telegram_name: $response['telegram_name'],
            telegram_username: $response['telegram_username'],
            telegram_token: $response['telegram_token'],
            customer_id: $response['customer_id'],
            attributes: $attributes,
        );
    }
}
