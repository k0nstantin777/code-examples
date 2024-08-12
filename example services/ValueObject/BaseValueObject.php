<?php

namespace App\Services\ValueObject;

use App\Services\ValueObject\Exceptions\InvalidSchemaException;

abstract class BaseValueObject
{
    public function __construct(
        protected array $attributes
    ) {
        if (false === $this->validateSchema()) {
            throw new InvalidSchemaException(array_keys($attributes), $this->getSchema());
        }

        $this->map();
    }

    protected function validateSchema() : bool
    {
        $schema = $this->getSchema();

        if (empty($schema)) {
            return true;
        }

        foreach ($this->attributes as $key => $value) {
            if (false === in_array($key, $schema, true)) {
                return false;
            }
        }

        return true;
    }

    abstract protected function getSchema() : array;
    abstract protected function map() : void;
}