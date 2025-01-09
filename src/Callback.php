<?php

namespace Xin\Support;

final class Callback
{
	/**
	 * 安全调用Callback
	 * @param callable $callback
	 * @param callable|null $failedCallback
	 * @return mixed
	 */
	public static function safeCall(callable $callback, callable $failedCallback = null)
	{
		return self::safeCallWithArgs($callback, [], $failedCallback);
	}

	/**
	 * 安全调用Callback，增加参数传递
	 * @param callable $callback
	 * @param array $args
	 * @param callable|null $failedCallback
	 * @return mixed
	 */
	public static function safeCallWithArgs(callable $callback, array $args = [], callable $failedCallback = null)
	{
		try {
			return call_user_func_array($callback, $args);
		} catch (\Throwable $e) {
			$failedCallback && call_user_func($failedCallback, $e);
		}

		return null;
	}
}
