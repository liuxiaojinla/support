<?php

namespace Xin\Support;

class Retry
{

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var int
	 */
	protected $count = 3;

	/**
	 * @var array
	 */
	protected $bindParams = null;

	/**
	 * Retry constructor.
	 *
	 * @param callable $callback
	 * @param int $count
	 */
	public function __construct($callback, $count = 3)
	{
		$this->callback = $callback;
		$this->count = $count;
	}

	/**
	 * @param mixed ...$args
	 * @return false|mixed
	 * @throws \Throwable
	 */
	public function invoke(...$args)
	{
		$exception = null;

		if (!is_null($this->bindParams)) {
			$args = $this->bindParams;
		}

		for ($i = 0; $i < $this->count; $i++) {
			try {
				return call_user_func_array($this->callback, $args);
			} catch (\Throwable $e) {
				$exception = $e;
			}
		}

		throw $exception;
	}

	/**
	 * 绑定调用参数
	 *
	 * @param array $params
	 * @return $this
	 */
	public function bindParams(...$params)
	{
		$this->bindParams = $params;

		return $this;
	}

	/**
	 * 绑定调用参数
	 *
	 * @param array $params
	 * @return $this
	 */
	public function bindParamsArray($params)
	{
		$this->bindParams = $params;

		return $this;
	}

	/**
	 * 实例化
	 *
	 * @param callable $callback
	 * @param int $count
	 * @return static
	 */
	public static function make($callback, $count = 3)
	{
		return new static($callback, $count);
	}

}
