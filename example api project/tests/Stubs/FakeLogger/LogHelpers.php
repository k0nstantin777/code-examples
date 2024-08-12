<?php

declare(strict_types=1);

namespace Tests\Stubs\FakeLogger;

trait LogHelpers
{
	/**
	 * Log an emergency message to the logs.
	 *
	 * @param string|\Stringable $message
	 * @param array $context
	 * @return void
	 */
    public function emergency(string|\Stringable $message, array $context = []): void
	{
        $this->log(__FUNCTION__, $message, $context);
    }

	/**
	 * Log an alert message to the logs.
	 *
	 * @param string|\Stringable $message
	 * @param array $context
	 * @return void
	 */
    public function alert(string|\Stringable $message, array $context = []): void
	{
        $this->log(__FUNCTION__, $message, $context);
    }

	/**
	 * Log a critical message to the logs.
	 *
	 * @param string|\Stringable $message
	 * @param array $context
	 * @return void
	 */
    public function critical(string|\Stringable $message, array $context = []): void
	{
        $this->log(__FUNCTION__, $message, $context);
    }

	/**
	 * Log an error message to the logs.
	 *
	 * @param string|\Stringable $message
	 * @param array $context
	 * @return void
	 */
    public function error(string|\Stringable $message, array $context = []): void
	{
        $this->log(__FUNCTION__, $message, $context);
    }

	/**
	 * Log a warning message to the logs.
	 *
	 * @param string|\Stringable $message
	 * @param array $context
	 * @return void
	 */
    public function warning(string|\Stringable $message, array $context = []): void
	{
        $this->log(__FUNCTION__, $message, $context);
    }

	/**
	 * Log a notice to the logs.
	 *
	 * @param string|\Stringable $message
	 * @param array $context
	 * @return void
	 */
    public function notice(string|\Stringable $message, array $context = []): void
	{
        $this->log(__FUNCTION__, $message, $context);
    }

	/**
	 * Log an informational message to the logs.
	 *
	 * @param string|\Stringable $message
	 * @param array $context
	 * @return void
	 */
    public function info(string|\Stringable $message, array $context = []): void
	{
        $this->log(__FUNCTION__, $message, $context);
    }

	/**
	 * Log a debug message to the logs.
	 *
	 * @param string|\Stringable $message
	 * @param array $context
	 * @return void
	 */
    public function debug(string|\Stringable $message, array $context = []): void
	{
        $this->log(__FUNCTION__, $message, $context);
    }
}
