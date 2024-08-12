<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;

class OrderCustomer extends BaseValueObject
{
    protected int $id;
    protected string $name;
    protected string $email;
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    protected function getSchema(): array
    {
        return [
            'id',
            'name',
            'email',
        ];
    }

    protected function map(): void
    {
        $this->id = $this->attributes['id'] ?? 0;
        $this->email = $this->attributes['email'];
        $this->name = $this->attributes['name'];
    }
}