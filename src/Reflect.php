<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\Support;

class Reflect
{

	const VISIBLE_PUBLIC = 0;

	const VISIBLE_PROTECTED = 1;

	const VISIBLE_PRIVATE = 2;

	/**
	 * 获取类方法可见范围
	 *
	 * @param mixed $class
	 * @param string $method
	 * @return int
	 * @throws \ReflectionException
	 */
	public static function getMethodVisible($class, $method)
	{
		$ref = new \ReflectionMethod($class, $method);
		if ($ref->isPublic()) {
			return self::VISIBLE_PUBLIC;
		} elseif ($ref->isProtected()) {
			return self::VISIBLE_PROTECTED;
		} else {
			return self::VISIBLE_PRIVATE;
		}
	}

	/**
	 * 方法可见范围
	 *
	 * @param mixed $class
	 * @param string $method
	 * @return int
	 */
	public static function methodVisible($class, $method)
	{
		try {
			return self::getMethodVisible($class, $method);
		} catch (\ReflectionException $e) {
		}

		return self::VISIBLE_PRIVATE;
	}

	/**
	 * 回退试调用类方法
	 *
	 * @param mixed $class
	 * @param array $methods
	 * @param array $args
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public static function fallbackCalls($class, $methods, $args = [])
	{
		foreach ($methods as $method) {
			if (self::VISIBLE_PUBLIC == self::getMethodVisible($class, $method)) {
				return call_user_func_array([$class, $method], $args);
			}
		}

		return null;
	}

}
