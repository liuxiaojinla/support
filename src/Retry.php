<?php

namespace Xin\Support;

class Retry
{

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var callable
	 */
	protected $when = null;

	/**
	 * @var \Throwable
	 */
	protected $exceptions = [];

	/**
	 * @var int|array
	 */
	protected $times = 3;

	/**
	 * Retry constructor.
	 *
	 * @param callable $callback
	 * @param callable|null $when
	 * @param int|array $times
	 */
	public function __construct(callable $callback, callable $when = null, $times = 3)
	{
		$this->callback = $callback;
		$this->when = $when;
		$this->times = $times;
	}

	/**
	 * 设置条件回调器
	 * @param callable $when
	 * @return $this
	 */
	public function when(callable $when)
	{
		$this->when = $when;

		return $this;
	}

	/**
	 * 执行
	 * @param bool $throw
	 * @return mixed
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function invoke($throw = true)
	{
		$exception = null;

		for ($attempts = 1; $attempts <= $this->times; $attempts++) {
			$this->exceptions[$attempts - 1] = null;

			try {
				return call_user_func($this->callback, $attempts);
			} catch (\Throwable $e) {
				$this->exceptions[$attempts - 1] = $exception = $e;

				if ($this->when && $result = call_user_func($this->when, $e, $attempts)) {
					return $result;
				}
			}
		}

		if ($throw) {
			/** @noinspection PhpUnhandledExceptionInspection */
			throw $exception;
		}

		return null;
	}

	/**
	 * 实例化
	 *
	 * @param callable $callback
	 * @param int $times
	 * @return static
	 */
	public static function make($callback, $times = 3)
	{
		return new static($callback, null, $times);
	}

}
