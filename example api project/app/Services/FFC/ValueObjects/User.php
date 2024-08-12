<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\FFC\Enums\UserType;
use App\Services\ValueObject\BaseValueObject;

class User extends BaseValueObject
{
    protected int $id;
    protected string $name;
    protected string $email;
    protected UserType $type;

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

    /**
     * @return UserType
     */
    public function getType(): UserType
    {
        return $this->type;
    }

    protected function getSchema(): array
    {
        return [
            'id',
            'name',
            'email',
            'type'
        ];
    }

    protected function map(): void
    {
        $this->id = $this->attributes['id'];
        $this->email = $this->attributes['email'];
        $this->name = $this->attributes['name'];
        $this->type = UserType::from($this->attributes['type']);
    }
}
