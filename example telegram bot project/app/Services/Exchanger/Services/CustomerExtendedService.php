<?php

namespace App\Services\Exchanger\Services;

use App\Services\Exchanger\Endpoints\GetCustomer;
use App\Services\Exchanger\Endpoints\GetCustomerExtended;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetCustomerRequestDto;
use App\Services\Exchanger\ValueObjects\Customer;
use App\Services\Exchanger\ValueObjects\CustomerExtended;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class CustomerExtendedService
{
    public function __construct(
        private readonly GetCustomerExtended $getCustomerExtendedEndpoint
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function get(GetCustomerRequestDto $dto) : CustomerExtended
    {
        return $this->getCustomerExtendedEndpoint->execute($dto);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getById(int $id) : CustomerExtended
    {
        return $this->get(new GetCustomerRequestDto(
            id: $id,
        ));
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getByEmail(string $email) : CustomerExtended
    {
        return $this->get(new GetCustomerRequestDto(
            email: $email,
        ));
    }

    public function getByEmailOrNull(string $email) : ?CustomerExtended
    {
        try {
            return $this->getByEmail($email);
        } catch (\Exception) {
            return null;
        }
    }

    public function getByIdOrNull(int $id) : ?CustomerExtended
    {
        try {
            return $this->getById($id);
        } catch (\Exception $e) {
            return null;
        }
    }
}
