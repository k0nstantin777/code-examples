<?php

declare(strict_types=1);

namespace Tests\Mocks\FakeLogger;

use Closure;
use Psr\Log\LoggerInterface;

/**
 * @mixin LogFake
 */
class ChannelFake implements LoggerInterface
{
    use LogHelpers;

    /**
     * @var LogFake
     */
    protected $log;

    /**
     * @var mixed
     */
    protected $name;

    /**
     * @param LogFake $log
     * @param mixed $name
     */
    public function __construct($log, $name)
    {
        $this->log = $log;

        $this->name = $name;
    }

    /**
     * @param mixed $level
     * @param string $message
     *
     * @return void
     */
    public function log($level, string|\Stringable $message, array $context = []) : void
    {
        $this->proxy(function () use ($level, $message, $context): void {
            $this->log->log($level, $message, $context);
        });
    }

    /**
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxy(
            /**
             * @return mixed
             */
            function () use ($method, $arguments) {
                return $this->log->{$method}(...$arguments);
            }
        );
    }

    /**
     * @param Closure $closure
     *
     * @return mixed
     */
    private function proxy($closure)
    {
        $this->log->setCurrentChannel($this->name);

        /** @var mixed */
        $result = $closure();

        $this->log->setCurrentChannel(null);

        return $result;
    }
}
