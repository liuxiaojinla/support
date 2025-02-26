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
	 * @var callable
	 */
	protected static $defaultDumperResolver = null;

	/**
	 * @var callable
	 */
	protected $dumperResolver = null;

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
			$second = (int)($second * 1000);
		}

		return $second;
	}

	/**
	 * 开始标记
	 * @param string $label
	 * @return object
	 */
	public function begin(string $label = 'default')
	{
		$beginTime = microtime(true);
		$this->times[$label] = [
			'begin' => $beginTime,
			'end' => $beginTime,
		];

		return new class($this, $this->name) {
			private DebugTime $debugTime;
			private $label;

			public function __construct(DebugTime $debugTime, $label)
			{
				$this->debugTime = $debugTime;
				$this->label = $label;
			}

			public function end(bool $asMillisecond = true, bool $print = true)
			{
				$this->debugTime->end($this->label, $asMillisecond, $print);
			}
		};
	}

	/**
	 * 结束标记
	 * @param string $label 标签名称
	 * @param bool $asMillisecond 转换为毫秒
	 * @return float|int
	 */
	public function end(string $label = 'default', bool $asMillisecond = true, bool $print = true)
	{
		$this->times[$label]['end'] = microtime(true);

		$timeConsuming = $this->time($label, $label, $asMillisecond);
		if ($print) {
			$this->dump($label, "time consuming: $timeConsuming".($asMillisecond?'ms':'us'));
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
		call_user_func($this->getDumperResolver(), $label, ...$messages);
	}

	/**
	 * 获取打印器
	 * @return callable|\Closure|null
	 */
	public function getDumperResolver()
	{
		if ($this->dumperResolver) {
			return $this->dumperResolver;
		}

		return static::getDefaultDumperResolver();
	}

	/**
	 * 设置打印器
	 * @param callable $dumperResolver
	 * @return void
	 */
	public function setDumperResolver(callable $dumperResolver)
	{
		$this->dumperResolver = $dumperResolver;
	}

	/**
	 * 获取默认的打印器
	 * @return callable|\Closure|null
	 */
	public static function getDefaultDumperResolver()
	{
		if (self::$defaultDumperResolver) {
			return self::$defaultDumperResolver;
		}

		return self::$defaultDumperResolver = function ($name, $label, ...$messages) {
			echo "[{$name}.{$label}] ";
			foreach ($messages as $message) {
				echo $message;
			}
			echo "\n";
		};
	}

	/**
	 * 设置默认的打印器
	 * @param callable $dumperResolver
	 * @return void
	 */
	public static function setDefaultDumperResolver(callable $dumperResolver)
	{
		self::$defaultDumperResolver = $dumperResolver;
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
	 * @param bool $asMillisecond
	 * @param bool $print
	 * @return void
	 */
	public static function endTime(string $label = 'default', bool $asMillisecond = true, bool $print = true)
	{
		return self::instance()->end($label, $asMillisecond, $print);
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
