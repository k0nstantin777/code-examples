<?php

namespace App\Services\ValueObject\Exceptions;

use Throwable;

class InvalidSchemaException extends \Exception
{
    /**
     * @throws \JsonException
     */
    public function __construct(
        private array $expectedSchema,
        private array $gotSchema,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($this->getFormattedMessage(), $code, $previous);
    }

    /**
     * @throws \JsonException
     */
    private function getFormattedMessage() : string
    {
        return sprintf(
            'Expected schema: %s, got: %s',
            json_encode($this->expectedSchema, JSON_THROW_ON_ERROR),
            json_encode($this->gotSchema, JSON_THROW_ON_ERROR),
        );
    }
}