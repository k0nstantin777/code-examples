<?php

namespace App\Services\ValueObject;

use App\Services\ValueObject\Exceptions\InvalidSchemaException;

abstract class BaseValueObject
{
    protected const NULLABLE_CHAR = '?';

	public function __construct(
        protected array $attributes
    ) {
        if (false === $this->validateSchema()) {
            throw new InvalidSchemaException($this->getSchema(), array_keys($attributes));
        }

        $this->map();
    }

    protected function validateSchema() : bool
    {
        $schema = $this->getSchema();

        if (empty($schema)) {
            return true;
        }

        foreach ($schema as $key) {
			if (str_starts_with($key, static::NULLABLE_CHAR)) {
				continue;
			}

			if (false === array_key_exists($key, $this->attributes)) {
                return false;
            }
        }

        return true;
    }

    abstract protected function getSchema() : array;
    abstract protected function map() : void;
}