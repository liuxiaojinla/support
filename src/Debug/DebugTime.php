<?php

namespace Xin\Support\Debug;

class DebugTime
{
	/**
	 * @var array
	 */
	protected $times = [];

	/**
	 * @param string $flag
	 * @return void
	 */
	public function start($flag = 'default')
	{
		$this->resolve($flag);
	}

	/**
	 * @param string $flag
	 * @return void
	 */
	protected function resolve($flag)
	{
		$this->times[$flag] = [
			'start' => microtime(true),
			'end'   => microtime(true),
		];
	}

	/**
	 * @param string $flag
	 * @param bool $asMillisecond
	 * @return float|int
	 */
	public function end($flag = 'default', $asMillisecond = false)
	{
		$this->times[$flag]['end'] = microtime(true);

		return $this->time($flag, $asMillisecond);
	}

	/**
	 * @param string $startFlag
	 * @param string $endFlag
	 * @param bool $asMillisecond
	 * @return float|int
	 */
	public function time($startFlag = 'default', $endFlag = null, $asMillisecond = false)
	{
		$endFlag = $endFlag ?: $startFlag;

		$second = $this->getEnd($endFlag) - $this->getStart($startFlag);

		if ($asMillisecond) {
			$second = (int)$second * 1000;
		}

		return $second;
	}

	/**
	 * @param string $flag
	 * @return float
	 */
	public function getEnd(string $flag)
	{
		return $this->times[$flag]['end'];
	}

	/**
	 * @param string $flag
	 * @return float
	 */
	public function getStart(string $flag)
	{
		return $this->times[$flag]['start'];
	}
}
