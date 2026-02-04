<?php

use Carbon\Carbon;
use Xin\Support\Path;
use Xin\Support\Proxy\HigherOrderTapProxy;
use Xin\Support\SQL;

if (!function_exists('tap')) {
	/**
	 * Call the given Closure with the given value then return the value.
	 *
	 * @param mixed $value
	 * @param callable|null $callback
	 * @return mixed
	 */
	function tap($value, ?callable $callback = null)
	{
		if (is_null($callback)) {
			return new HigherOrderTapProxy($value);
		}

		$callback($value);

		return $value;
	}
}

if (!function_exists('value')) {
	/**
	 * Return the default value of the given value.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

if (!function_exists('blank')) {
	/**
	 * Determine if the given value is "blank".
	 *
	 * @param mixed $value
	 * @return bool
	 */
	function blank($value)
	{
		if (is_null($value)) {
			return true;
		}

		if (is_string($value)) {
			return trim($value) === '';
		}

		if (is_numeric($value) || is_bool($value)) {
			return false;
		}

		if ($value instanceof Countable) {
			return count($value) === 0;
		}

		return empty($value);
	}
}

if (!function_exists('filled')) {
	/**
	 * Determine if a value is "filled".
	 *
	 * @param mixed $value
	 * @return bool
	 */
	function filled($value)
	{
		return !blank($value);
	}
}

if (!function_exists('windows_os')) {
	/**
	 * Determine whether the current environment is Windows based.
	 *
	 * @return bool
	 * @deprecated
	 * @see \Xin\Support\OS::isWindows()
	 */
	function windows_os()
	{
		return strtolower(substr(PHP_OS, 0, 3)) === 'win';
	}
}

if (!function_exists('object_get')) {
	/**
	 * Get an item from an object using "dot" notation.
	 *
	 * @param object $object
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function object_get($object, $key, $default = null)
	{
		if (is_null($key) || trim($key) == '') {
			return $object;
		}

		foreach (explode('.', $key) as $segment) {
			if (!is_object($object) || !isset($object->{$segment})) {
				return value($default);
			}

			$object = $object->{$segment};
		}

		return $object;
	}
}

if (!function_exists('build_mysql_distance_field')) {
	/**
	 * 生成计算位置字段
	 *
	 * @param float $longitude
	 * @param float $latitude
	 * @param string $lng_name
	 * @param string $lat_name
	 * @param string $as_name
	 * @return string
	 * @deprecated
	 * @see SQL::mysqlDistance
	 */
	function build_mysql_distance_field(
		$longitude, $latitude,
		$lng_name = 'longitude', $lat_name = 'latitude',
		$as_name = 'distance'
	)
	{
		return SQL::mysqlDistance($longitude, $latitude, $lng_name, $lat_name, $as_name);
	}
}

if (!function_exists('get_class_const_list')) {
	/**
	 * 获取常量列表
	 *
	 * @param string $class
	 * @return array|bool
	 */
	function get_class_const_list($class)
	{
		try {
			return (new ReflectionClass($class))->getConstants();
		} catch (ReflectionException $e) {
		}

		return false;
	}
}

if (!function_exists('get_const_value')) {
	/**
	 * 获取常量列表
	 *
	 * @param string $class
	 * @param string $name
	 * @return mixed
	 */
	function get_const_value($class, $name)
	{
		try {
			$ref = new ReflectionClass($class);
			if (!$ref->hasConstant($name)) {
				return null;
			}

			return $ref->getConstant($name);
		} catch (ReflectionException $e) {
		}

		return false;
	}
}

if (!function_exists('const_exist')) {
	/**
	 * 类常量是否存在
	 *
	 * @param string $class
	 * @param string $name
	 * @return bool
	 */
	function const_exist($class, $name)
	{
		try {
			return (new ReflectionClass($class))->hasConstant($name);
		} catch (ReflectionException $e) {
		}

		return false;
	}
}

if (!function_exists('now')) {
	/**
	 * 获取当前时间实例
	 *
	 * @param DateTimeZone|string|null $tz $tz
	 * @return Carbon|DateTime
	 */
	function now($tz = null)
	{
		return \Xin\Support\Time::now($tz);
	}
}

if (!function_exists('class_namespace')) {
	/**
	 * 获取命名空间名
	 *
	 * @param mixed $class 类名
	 * @return string
	 */
	function class_namespace($class): string
	{
		$class = is_object($class) ? get_class($class) : $class;
		$lastSlashIndex = strrpos($class, '\\');
		return substr($class, 0, $lastSlashIndex);
	}
}

if (!function_exists('class_basename')) {
	/**
	 * 获取类名(不包含命名空间)
	 *
	 * @param mixed $class 类名
	 * @return string
	 */
	function class_basename($class): string
	{
		$class = is_object($class) ? get_class($class) : $class;
		return basename(str_replace('\\', '/', $class));
	}
}

if (!function_exists('class_uses_recursive')) {
	/**
	 *获取一个类里所有用到的trait，包括父类的
	 *
	 * @param mixed $class 类名
	 * @return array
	 */
	function class_uses_recursive($class): array
	{
		if (is_object($class)) {
			$class = get_class($class);
		}

		$results = [];
		$classes = array_merge([$class => $class], class_parents($class));
		foreach ($classes as $class) {
			$results += trait_uses_recursive($class);
		}

		return array_unique($results);
	}
}

if (!function_exists('trait_uses_recursive')) {
	/**
	 * 获取一个trait里所有引用到的trait
	 *
	 * @param string $trait Trait
	 * @return array
	 */
	function trait_uses_recursive(string $trait): array
	{
		$traits = class_uses($trait);
		foreach ($traits as $trait) {
			$traits += trait_uses_recursive($trait);
		}

		return $traits;
	}
}

if (!function_exists('with')) {
	/**
	 * 返回给定的值，可以选择通过给定的回调传递。
	 *
	 * @template TValue
	 * @template TReturn
	 *
	 * @param TValue $value
	 * @param (callable(TValue): (TReturn))|null $callback
	 * @return ($callback is null ? TValue : TReturn)
	 */
	function with($value, ?callable $callback = null)
	{
		return is_null($callback) ? $value : $callback($value);
	}
}

if (!function_exists('suffix')) {
	/**
	 * 获取文件后缀
	 * @param string $file
	 * @return string
	 */
	function path_suffix($file)
	{
		return Path::suffix($file);
	}
}

if (!function_exists('replace_suffix')) {
	/**
	 * 替换文件后缀
	 * @param string $file
	 * @return string
	 */
	function path_replace_suffix($file, $suffix)
	{
		return Path::replaceSuffix($file, $suffix);
	}
}

if (!function_exists('join_paths')) {
	/**
	 * 拼接路径
	 * @param mixed ...$paths
	 * @return string
	 */
	function join_paths(...$paths)
	{
		return Path::joins(...$paths);
	}
}

if (!function_exists('path_joins')) {
	/**
	 * 拼接路径
	 * @param mixed ...$paths
	 * @return string
	 */
	function path_joins(...$paths)
	{
		return Path::joins(...$paths);
	}
}
