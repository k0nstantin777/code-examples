<?php

namespace App\Services\TelegramBot\Services;

use App\Domains\User\Models\User;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\Services\CustomerService;
use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\Exchanger\ValueObjects\ExchangeDirection;
use App\Services\Exchanger\ValueObjects\ExchangeFormAttribute;
use App\Services\TelegramBot\Storages\ExchangeRequestStorage;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class TelegramBotExchangeRequestService
{
    public function __construct(
        private readonly ExchangeRequestStorage $exchangeRequestStorage,
        private readonly CustomerService $customerService,
        private readonly TelegramBotRemoteExchangeRequestService $telegramBotRemoteExchangeRequestService,
        private readonly TelegramBotExchangeDirectionService $telegramBotExchangeDirectionService,
    ) {
    }

    public function getByUserId(int $userId) : ?ExchangeRequest
    {
        return $this->exchangeRequestStorage->get($userId);
    }

    public function save(ExchangeRequest $exchangeRequest) : void
    {
        $this->exchangeRequestStorage->save($exchangeRequest->getUser()->id, $exchangeRequest);
    }

    public function delete(ExchangeRequest $exchangeRequest) : void
    {
        $this->exchangeRequestStorage->remove($exchangeRequest->getUser()->id);
    }

    public function resetForUser(User $user) : void
    {
        $exchangeRequest = $this->getByUserId($user->id);

        if ($exchangeRequest) {
            $this->delete($exchangeRequest);
        }
    }

    public function getCustomerEmail(ExchangeRequest $exchangeRequest) : string
    {
        if ($exchangeRequest->getEmail()) {
            return $exchangeRequest->getEmail();
        }

        $exchangeUserId = $exchangeRequest->getUser()->getExchangerUserIdOrNull();

        if (!$exchangeUserId) {
            return '';
        }

        $customer = $this->customerService->getByIdOrNull($exchangeUserId);

        return $customer->email ?? '';
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getExchangeDirection(ExchangeRequest $exchangeRequest) : ?ExchangeDirection
    {
        return $this->telegramBotExchangeDirectionService->getByExchangeRequest($exchangeRequest);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getNextRequiredFormAttribute(ExchangeRequest $exchangeRequest) : ?ExchangeFormAttribute
    {
        $exchangeDirection = $this->getExchangeDirection($exchangeRequest);

        if (!$exchangeDirection) {
            return null;
        }

        $filledFormAttributes = $exchangeRequest->getFilledFormAttributes();
        foreach ($exchangeDirection->formAttributes as $formAttribute) {
            $code = $formAttribute->code;
            $filledAttribute = $filledFormAttributes[$code] ?? null;

            if (null === $filledAttribute) {
                return $formAttribute;
            }
        }

        return null;
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getRemoteExchangeRequest(ExchangeRequest $exchangeRequest) : ?ActiveExchangeRequest
    {
        return $this->telegramBotRemoteExchangeRequestService->get($exchangeRequest);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function refreshRemoteExchangeRequest(ExchangeRequest $exchangeRequest) : ?ActiveExchangeRequest
    {
        return $this->telegramBotRemoteExchangeRequestService->refresh($exchangeRequest);
    }
}
