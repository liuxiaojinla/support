<?php

namespace Xin\Support;

/**
 * 反射工具类
 */
final class Reflect
{

	// 方法可见
	public const VISIBLE_PUBLIC = 0;

	// 方法可见为保护
	public const VISIBLE_PROTECTED = 1;

	// 方法可见为私有
	public const VISIBLE_PRIVATE = 2;

	/**
	 * 获取类方法可见范围
	 *
	 * @param mixed $objectOrMethod
	 * @param string $method
	 * @return int
	 * @throws \ReflectionException
	 * @deprecated
	 */
	public static function getMethodVisible($objectOrMethod, $method, $throw = true)
	{
		try {
			$ref = new \ReflectionMethod($objectOrMethod, $method);
			if ($ref->isPublic()) {
				return self::VISIBLE_PUBLIC;
			}

			if ($ref->isProtected()) {
				return self::VISIBLE_PROTECTED;
			}

			return self::VISIBLE_PRIVATE;
		} catch (\ReflectionException $e) {
			if ($throw) {
				throw $e;
			}
		}

		return self::VISIBLE_PRIVATE;
	}

	/**
	 * 方法可见范围 - 无异常模式
	 *
	 * @param string|object $objectOrMethod
	 * @param string $method
	 * @return int
	 * @noinspection PhpDocMissingThrowsInspection
	 * @deprecated
	 */
	public static function methodVisible($objectOrMethod, $method)
	{
		return self::getMethodVisible($objectOrMethod, $method, false);
	}

	/**
	 * 是否是公共方法
	 * @param string|object $objectOrMethod
	 * @param string|null $method
	 * @return bool
	 * @throws \ReflectionException
	 */
	public function isPublicMethod($objectOrMethod, $method = null)
	{
		$ref = new \ReflectionMethod($objectOrMethod, $method);
		return $ref->isPublic();
	}

	/**
	 * 是否是保护方法
	 * @param string|object $objectOrMethod
	 * @param string|null $method
	 * @return bool
	 * @throws \ReflectionException
	 */
	public function isProtectedMethod($objectOrMethod, $method = null)
	{
		$ref = new \ReflectionMethod($objectOrMethod, $method);
		return $ref->isProtected();
	}

	/**
	 * 是否是私有方法
	 * @param string|object $objectOrMethod
	 * @param string|null $method
	 * @return bool
	 * @throws \ReflectionException
	 */
	public function isPrivateMethod($objectOrMethod, $method = null)
	{
		$ref = new \ReflectionMethod($objectOrMethod, $method);
		return $ref->isPrivate();
	}

	/**
	 * 获取方法的修饰符
	 * 使用 ReflectionMethod::getModifiers() 可以返回一个位掩码（bitmask），表示方法的访问修饰符和非访问修饰符。
	 * 这个返回值是一个整数，你可以使用位运算符来检查特定的修饰符。
	 * // 检查方法是否是 public
	 * if ($modifiers & ReflectionMethod::IS_PUBLIC) {
	 * echo "Method is public.\n";
	 * }
	 *
	 * // 检查方法是否是 static
	 * if ($modifiers & ReflectionMethod::IS_STATIC) {
	 * echo "Method is static.\n";
	 * }
	 *
	 * // 检查方法是否是 final
	 * if ($modifiers & ReflectionMethod::IS_FINAL) {
	 * echo "Method is final.\n";
	 * }
	 *
	 * // 检查方法是否是 abstract
	 * if ($modifiers & ReflectionMethod::IS_ABSTRACT) {
	 * echo "Method is abstract.\n";
	 * }
	 * @param string|object $objectOrMethod
	 * @param string|null $method
	 * @param bool $throw
	 * @return int
	 * @throws \ReflectionException
	 */
	public static function getMethodModifiers($objectOrMethod, $method = null, $throw = false)
	{
		try {
			$ref = new \ReflectionMethod($objectOrMethod, $method);
			return $ref->getModifiers();
		} catch (\ReflectionException $e) {
			if ($throw) {
				throw $e;
			}
		}

		return \ReflectionMethod::IS_PRIVATE;
	}

	/**
	 * 获取类的方法
	 * @param string|object $objectOrClass
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function getMethods($objectOrClass, $filter = null)
	{
		$reflectionClass = new \ReflectionClass($objectOrClass);
		return $reflectionClass->getMethods($filter);
	}

	/**
	 * 获取类或对象的公共方法
	 * @param string|object $objectOrClass
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function getPublicMethods($objectOrClass)
	{
		return self::getMethods($objectOrClass, \ReflectionMethod::IS_PUBLIC);
	}

	/**
	 * 获取类或对象的受保护方法
	 * @param string|object $objectOrClass
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function getProtectedMethods($objectOrClass)
	{
		return self::getMethods($objectOrClass, \ReflectionMethod::IS_PROTECTED);
	}

	/**
	 * 获取类或对象的私有方法
	 * @param string|object $objectOrClass
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function getPrivateMethods($objectOrClass)
	{
		return self::getMethods($objectOrClass, \ReflectionMethod::IS_PRIVATE);
	}

	/**
	 * 获取类或对象的最终方法
	 * @param string|object $objectOrClass
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function getFinalMethods($objectOrClass)
	{
		return self::getMethods($objectOrClass, \ReflectionMethod::IS_FINAL);
	}

	/**
	 * 获取类或对象的抽象方法
	 * @param string|object $objectOrClass
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function getAbstractMethods($objectOrClass)
	{
		return self::getMethods($objectOrClass, \ReflectionMethod::IS_ABSTRACT);
	}

	/**
	 * 回退试调用类方法
	 *
	 * @param mixed $class
	 * @param array $methods
	 * @param array $args
	 * @return mixed
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function fallbackCalls($class, array $methods, array $args = [])
	{
		foreach ($methods as $method) {
			if (self::VISIBLE_PUBLIC === self::methodVisible($class, $method)) {
				return call_user_func_array([$class, $method], $args);
			}
		}

		return null;
	}

	/**
	 * 获取类属性
	 * @param string|object $class
	 * @param string $propertyName
	 * @return \ReflectionProperty
	 * @throws \ReflectionException
	 */
	public static function getProperty($class, $propertyName)
	{
		$property = new \ReflectionProperty($class, $propertyName);
		$property->setAccessible(true);

		return $property;
	}

	/**
	 * 获取属性值
	 * @param object $classInstance
	 * @param string $property
	 * @return mixed
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function getPropertyValue($classInstance, $property, $default = null, $throw = false)
	{
		try {
			return self::getProperty($classInstance, $property)->getValue($classInstance);
		} catch (\ReflectionException $e) {
			if ($throw) {
				throw $e;
			}
		}

		return $default;
	}

	/**
	 * 获取属性值
	 * @param object $classInstance
	 * @param string $property
	 * @return mixed
	 * @deprecated
	 */
	public static function propertyValue($classInstance, $property, $default = null)
	{
		try {
			return self::getPropertyValue($classInstance, $property);
		} catch (\ReflectionException $e) {
			return $default;
		}
	}

	/**
	 * 属性是否存在
	 * @param string|object $objectOrClass
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function hasProperty($objectOrClass, $propertyName, $isStatic = null)
	{
		$reflectionClass = new \ReflectionClass($objectOrClass);
		// 使用 hasProperty 方法来检测静态变量
		if ($reflectionClass->hasProperty($propertyName)) {
			if ($isStatic === null) {
				return true;
			}

			$property = $reflectionClass->getProperty($propertyName);
			if ($isStatic) {
				return $property->isStatic();
			} else {
				return !$property->isStatic();
			}
		}

		return false;
	}

	/**
	 * @param string|object $objectOrClass
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function hasDynamicProperty($objectOrClass, $propertyName)
	{
		return self::hasProperty($objectOrClass, $propertyName, false);
	}

	/**
	 * @param string|object $objectOrClass
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function hasStaticProperty($objectOrClass, $propertyName)
	{
		return self::hasProperty($objectOrClass, $propertyName, true);
	}

	/**
	 * 设置属性值
	 * @param object $classInstance
	 * @param string $propertyName
	 * @param mixed $value
	 * @return \ReflectionProperty
	 * @throws \ReflectionException
	 */
	public static function setPropertyValue($classInstance, $propertyName, $value)
	{
		$property = self::getProperty($classInstance, $propertyName);

		if (is_callable($value)) {
			$value = $value($property->getValue());
		}

		$property->setValue($classInstance, $value);

		return $property;
	}

	/**
	 * 是否是生成器函数
	 * @param callable $callback
	 * @param bool $throw
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function isGenerator(callable $callback, $throw = false)
	{
		try {
			if (is_array($callback)) {
				// 对于数组形式的回调，例如 [$object, 'methodName']
				$reflection = new \ReflectionMethod($callback[0], $callback[1]);
			} else {
				// 对于普通函数
				$reflection = new \ReflectionFunction($callback);
			}
			return $reflection->isGenerator();
		} catch (\ReflectionException $e) {
			if ($throw) {
				throw $e;
			}

			return false;
		}
	}
}
