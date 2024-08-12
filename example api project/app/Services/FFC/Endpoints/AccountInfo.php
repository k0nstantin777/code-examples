<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\AccountInfoRequestDto;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use App\Services\FFC\ValueObjects\AccountInfo as AccountInfoValueObject;
use Illuminate\Validation\ValidationException;

class AccountInfo extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return AccountInfoValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function execute(...$arguments): AccountInfoValueObject
    {
        /* @var AccountInfoRequestDto $accountInfoRequestDto */
        [$accountInfoRequestDto] = $arguments;

         $response = $this->jsonRpcClient->send('account', [
            'user_id' => $accountInfoRequestDto->getUserId(),
            'includes' => $accountInfoRequestDto->getIncludes(),
         ]);

         return new AccountInfoValueObject($response);
    }
}
