<?php

namespace App\Services\Exchanger\Endpoints\Cached;

use App\Services\Exchanger\Endpoints\GetTelegramBot as BaseGetTelegramBot;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\JsonRpcClient;
use App\Services\Exchanger\RequestDTOs\GetTelegramBotRequestDto;
use App\Services\Exchanger\Storages\TelegramBot\TelegramBotStorage;
use App\Services\Exchanger\ValueObjects\TelegramBot;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class GetTelegramBot extends BaseGetTelegramBot
{
    public function __construct(
        JsonRpcClient $jsonRpcClient,
        private readonly TelegramBotStorage $telegramBotStorage
    ) {
        parent::__construct($jsonRpcClient);
    }

    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : TelegramBot
    {
        /** @var GetTelegramBotRequestDto $dto */
        [$dto] = $arguments;

        $key = $this->getKey($dto);

        $result = $this->telegramBotStorage->get($key);

        if (!$result) {
            $result = parent::execute($dto);

            $this->telegramBotStorage->save($key, $result);
        }

        return $result;
    }

    private function getKey(GetTelegramBotRequestDto $dto) : string
    {
        return json_encode($dto->toArray());
    }
}
