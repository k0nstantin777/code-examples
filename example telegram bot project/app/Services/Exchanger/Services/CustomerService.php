<?php

namespace App\Services\Exchanger\Services;

use App\Services\Exchanger\Endpoints\GetCustomer;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetCustomerRequestDto;
use App\Services\Exchanger\ValueObjects\Customer;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class CustomerService
{
    public function __construct(
        private readonly GetCustomer $getCustomerEndpoint
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function get(GetCustomerRequestDto $dto) : Customer
    {
        return $this->getCustomerEndpoint->execute($dto);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function getById(int $id) : Customer
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
    public function getByEmail(string $email) : Customer
    {
        return $this->get(new GetCustomerRequestDto(
            email: $email,
        ));
    }

    public function getByEmailOrNull(string $email) : ?Customer
    {
        try {
            return $this->getByEmail($email);
        } catch (\Exception) {
            return null;
        }
    }

    public function getByIdOrNull(int $id) : ?Customer
    {
        try {
            return $this->getById($id);
        } catch (\Exception) {
            return null;
        }
    }
}
