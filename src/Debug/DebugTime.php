<?php

namespace Xin\Support\Debug;

use Xin\Support\Callback;
use Xin\Support\Counter;

class DebugTime
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $times = [];

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @var Counter
	 */
	protected static $counter;

	/**
	 * @param string $name
	 */
	public function __construct(string $name = '')
	{
		$this->name = $name ?: "DebugTime." . self::newId();
	}


	/**
	 * 计算耗时时间
	 * @param string $beginFlag
	 * @param string|null $endFlag
	 * @param bool $asMillisecond
	 * @return float|int
	 */
	public function time(string $beginFlag = 'default', string $endFlag = null, bool $asMillisecond = false)
	{
		$endFlag = $endFlag ?: $beginFlag;

		$second = $this->getEnd($endFlag) - $this->getBegin($beginFlag);

		if ($asMillisecond) {
			$second = (int)$second * 1000;
		}

		return $second;
	}

	/**
	 * 开始标记
	 * @param string $label
	 * @return void
	 */
	public function begin(string $label = 'default')
	{
		$beginTime = microtime(true);
		$this->times[$label] = [
			'begin' => $beginTime,
			'end' => $beginTime,
		];
	}

	/**
	 * 结束标记
	 * @param string $label 标签名称
	 * @param bool $asMillisecond 转换为毫秒
	 * @return float|int
	 */
	public function end(string $label = 'default', bool $asMillisecond = false, bool $print = true)
	{
		$this->times[$label]['end'] = microtime(true);

		$timeConsuming = $this->time($label, $label, $asMillisecond);
		if ($print) {
			$this->dump($label, "time consuming: $timeConsuming");
		}

		return $timeConsuming;
	}

	/**
	 * 获取结束时间
	 * @param string $label
	 * @return float
	 */
	public function getEnd(string $label)
	{
		return $this->times[$label]['end'];
	}

	/**
	 * 获取开始时间
	 * @param string $label
	 * @return float
	 */
	public function getBegin(string $label)
	{
		return $this->times[$label]['begin'];
	}


	/**
	 * 打印控制台输出
	 * @param string $label
	 * @param ...$messages
	 * @return void
	 */
	protected function dump(string $label, ...$messages)
	{
		echo "[{$this->name}.{$label}] ";
		foreach ($messages as $message) {
			echo $message;
		}
		echo "\n";
	}

	/**
	 * @return self
	 */
	public static function instance()
	{
		if (!self::$instance) {
			self::$instance = new self('global');
		}

		return self::$instance;
	}

	/**
	 * @param string $label
	 * @return void
	 */
	public static function beginTime(string $label = 'default')
	{
		self::instance()->begin($label);
	}

	/**
	 * @param string $label
	 * @return void
	 */
	public static function endTime(string $label = 'default')
	{
		self::instance()->end($label);
	}

	/**
	 * @param callable $callback
	 * @param string $label
	 * @return void
	 */
	public static function call(callable $callback, string $label = 'default')
	{
		self::instance()->begin($label);
		Callback::safeCall($callback);
		self::instance()->end($label);
	}

	/**
	 * @return float|int
	 */
	protected static function newId()
	{
		if (self::$counter === null) {
			self::$counter = new Counter();
		}

		return self::$counter->get(1);
	}
}
