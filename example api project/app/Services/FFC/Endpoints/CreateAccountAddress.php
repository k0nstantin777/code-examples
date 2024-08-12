<?php

namespace App\Services\FFC\Endpoints;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateAccountAddressRequestDto;
use Illuminate\Validation\ValidationException;

class CreateAccountAddress extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @return array
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function execute(...$arguments): array
    {
        /* @var CreateAccountAddressRequestDto $dto */
        [$dto] = $arguments;

        return $this->jsonRpcClient->send('account/addresses.store', [
            'user_id' => $dto->userId,
            'postal' => $dto->postal,
            'state' => $dto->state,
            'address1' => $dto->address1,
            'address2' => $dto->address2,
            'city' => $dto->city,
            'telephone' => $dto->telephone,
            'firstname' => $dto->firstname,
            'lastname' => $dto->lastname,
            'company' => $dto->company,
            'email' => $dto->email,
            'salutation' => $dto->salutation,
        ]);
    }
}
