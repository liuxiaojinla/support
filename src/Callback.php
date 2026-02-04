<?php

namespace Xin\Support;

use Throwable;

final class Callback
{
	/**
	 * 安全调用Callback
	 * @param callable $callback
	 * @param callable|null $failedCallback
	 * @return mixed
	 */
	public static function safeCall(callable $callback, ?callable $failedCallback = null)
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
	public static function safeCallWithArgs(callable $callback, array $args = [], ?callable $failedCallback = null)
	{
		try {
			return call_user_func_array($callback, $args);
		} catch (Throwable $e) {
			$failedCallback && call_user_func($failedCallback, $e);
		}

		return null;
	}

	/**
	 * 在 ... 之前附加 Callback
	 * @param callable $callback
	 * @param callable|null $originalCallback
	 * @return callable
	 */
	public static function attachBefore(callable $callback, ?callable $originalCallback)
	{
		return function (...$args) use ($callback, $originalCallback) {
			$result = call_user_func_array($callback, $args);

			if (!$originalCallback) {
				return call_user_func_array($originalCallback, $args);
			}

			return $result;
		};
	}

	/**
	 * 在 ... 之后附加 Callback
	 * @param callable $callback
	 * @param callable|null $originalCallback
	 * @return callable
	 */
	public static function attachAfter(callable $callback, ?callable $originalCallback)
	{
		return function (...$args) use ($callback, $originalCallback) {
			$result = null;
			if ($originalCallback) {
				$result = call_user_func_array($originalCallback, $args);
			}

			$temp = call_user_func_array($callback, $args);
			if ($temp !== null) {
				$result = $temp;
			}

			return $result;
		};
	}
}
