<?php

namespace App\Services\Exchanger\Endpoints\Cached;

use App\Services\Exchanger\Endpoints\ListTelegramBots as BaseListTelegramBots;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\JsonRpcClient;
use App\Services\Exchanger\RequestDTOs\ListRequestDto;
use App\Services\Exchanger\Storages\TelegramBot\TelegramBotListStorage;
use App\Services\Exchanger\ValueObjects\TelegramBotsList;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ListTelegramBots extends BaseListTelegramBots
{
    public function __construct(
        JsonRpcClient $jsonRpcClient,
        private readonly TelegramBotListStorage $telegramBotListStorage
    ) {
        parent::__construct($jsonRpcClient);
    }

    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : TelegramBotsList
    {
        /** @var ListRequestDto $dto */
        [$dto] = $arguments;

        $key = $this->getKey($dto);

        $result = $this->telegramBotListStorage->get($key);

        if (!$result) {
            $result = parent::execute($dto);

            $this->telegramBotListStorage->save($key, $result);
        }

        return $result;
    }

    private function getKey(ListRequestDto $dto) : string
    {
        return json_encode($dto->toArray());
    }
}
