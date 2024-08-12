<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;

class Category extends BaseValueObject
{
    private int $id;
    private string $code;
    private string $label;

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
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    protected function getSchema(): array
    {
        return [
            'id',
            'code',
            'label',
        ];
    }

    protected function map(): void
    {
        $this->id = $this->attributes['id'];
        $this->code = $this->attributes['code'];
        $this->label = $this->attributes['label'];
    }
}
