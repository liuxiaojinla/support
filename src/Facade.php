<?php

namespace Xin\Support;

abstract class Facade
{
	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		return call_user_func_array([static::getFacadeAccessor(), $name], $arguments);
	}

	/**
	 * @return mixed
	 */
	abstract public static function getFacadeAccessor();
}
