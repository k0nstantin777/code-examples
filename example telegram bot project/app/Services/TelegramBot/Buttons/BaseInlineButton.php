<?php

namespace App\Services\TelegramBot\Buttons;

use App\Services\TelegramBot\Helpers\ButtonParamHelper;
use Telegram\Bot\Keyboard\Button;
use Telegram\Bot\Keyboard\Keyboard;

abstract class BaseInlineButton
{
    private const ALLOWED_HASH_LENGTH = 64;

    public function __construct(
        private readonly ButtonParamHelper $buttonParamHelper,
    ) {
    }

    public function make(string $name, array $params = []) : Button
    {
        if (!$this->validateParams($params)) {
            throw new \InvalidArgumentException('Invalid button`s params');
        }

        return Keyboard::inlineButton([
            'text' => $name,
            'callback_data' => $this->hash($params),
        ]);
    }

    public function isPressed(string $hash) : bool
    {
        $decodedParams = $this->buttonParamHelper->decode($hash);

        return isset($decodedParams[0]) && $decodedParams[0] === $this->getPrefix();
    }

    protected function hash(array $params) : string
    {
        $hash = $this->buttonParamHelper->encode(array_merge([
            $this->getPrefix(),
        ], $params));

        if (strlen($hash) > self::ALLOWED_HASH_LENGTH) {
            throw new \InvalidArgumentException(
                'Hash: ' . $hash . 'must be less or equal ' . self::ALLOWED_HASH_LENGTH . ' bytes'
            );
        }

        return $hash;
    }

    public function parseParams(string $hash) : array
    {
        $decodedParams = $this->buttonParamHelper->decode($hash);
        array_shift($decodedParams);

        return $decodedParams;
    }

    abstract protected function getPrefix() : string;
    abstract protected function validateParams(array $params) : bool;
}
