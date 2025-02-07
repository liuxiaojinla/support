<?php

namespace Xin\Support;

class Counter
{
	/**
	 * @var int|float
	 */
	protected $value = 0;

	/**
	 * 构造函数
	 * @param int|float $value
	 */
	public function __construct($value = 0)
	{
		$this->value = $value;
	}

	/**
	 * 增加值
	 * @param int|float $value
	 * @return int|float
	 */
	public function increment($value = 1)
	{
		$this->value += $value;
		return $this->value;
	}

	/**
	 * 获取值
	 * @param float|int $increment
	 * @return float|int
	 */
	public function get($increment = 0)
	{
		if ($increment > 0) {
			$this->increment($increment);
		}

		return $this->value;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->value;
	}

	/**
	 * @param ...$args
	 * @return float|int
	 */
	public function __invoke(...$args)
	{
		return $this->get(...$args);
	}
}
