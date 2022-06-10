<?php

namespace Xin\Support;

class LimitThrottle
{

	/**
	 * @var array
	 */
	protected $limits = [];

	/**
	 * @var callable
	 */
	protected $valueCallback;

	/**
	 * @var callable
	 */
	protected $thenCallback;

	/**
	 * LimitThrottle constructor.
	 *
	 * @param array $limits
	 * @param callable $valueCallback
	 * @param callable $thenCallback
	 */
	public function __construct(array $limits, callable $valueCallback, callable $thenCallback)
	{
		$this->limits = array_reverse($limits);
		$this->valueCallback = $valueCallback;
		$this->thenCallback = $thenCallback;
	}

	/**
	 * 开始执行
	 *
	 * @return mixed
	 */
	public function exec()
	{
		$value = $this->getValue();
		if ($value == 1) {
			return $this->call($value);
		}

		$limit = $this->getLimit($value);
		if ($limit) {
			if ($value % $limit === 0) {
				return $this->call($value);
			}
		}

		return null;
	}

	/**
	 * 获取一个限制数
	 *
	 * @param int $value
	 * @return int
	 */
	protected function getLimit($value)
	{
		foreach ($this->limits as $limit) {
			if ($value >= $limit) {
				return $limit;
			}
		}

		return 0;
	}

	/**
	 * 获取值
	 *
	 * @return int
	 */
	protected function getValue()
	{
		try {
			return call_user_func($this->valueCallback);
		} catch (\Throwable $e) {
		}

		return 0;
	}

	/**
	 * 调用处理函数
	 *
	 * @param int $value
	 * @return mixed
	 */
	protected function call($value)
	{
		try {
			return call_user_func($this->thenCallback, $this->limits, $value);
		} catch (\Throwable $e) {
		}

		return null;
	}

	/**
	 * 一般的限制节滤器
	 *
	 * @param callable $valueCallback
	 * @param callable $thenCallback
	 * @return mixed|null
	 */
	public static function general(callable $valueCallback, callable $thenCallback)
	{
		return (new static(
			[50, 100, 500, 1000, 5000, 10000, 50000, 100000],
			$valueCallback, $thenCallback
		))->exec();
	}

}
