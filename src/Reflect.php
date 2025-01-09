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
	 * 内部方法，是否抛出异常
	 * @param callable $callback
	 * @param mixed $default
	 * @param bool $throw
	 * @return mixed|null
	 * @throws \ReflectionException
	 */
	protected static function throwIf($callback, $default = null, $throw = false)
	{
		try {
			return $callback();
		} catch (\ReflectionException $e) {
			if ($throw) {
				throw $e;
			}
		}

		return value($default);
	}

	/**
	 * 转换为 ReflectionClass
	 * @param object|string $objectOrClass
	 * @return \ReflectionClass
	 * @throws \ReflectionException
	 */
	public static function asClassRef($objectOrClass)
	{
		return (new \ReflectionClass($objectOrClass));
	}

	/**
	 * 转换为 ReflectionMethod
	 * @param object|string $objectOrClass
	 * @param string $methodName
	 * @return \ReflectionMethod
	 * @throws \ReflectionException
	 */
	public static function asMethodRef($objectOrClass, $methodName)
	{
		$ref = new \ReflectionMethod($objectOrClass, $methodName);
		/** @noinspection PhpExpressionResultUnusedInspection */
		$ref->setAccessible(true);

		return $ref;
	}

	/**
	 * 转换为 ReflectionFunction
	 * @param Closure|string $function
	 * @return \ReflectionFunction
	 * @throws \ReflectionException
	 */
	public static function asFunctionRef($function)
	{
		return new \ReflectionFunction($function);
	}

	/**
	 * 转换为 ReflectionProperty
	 * @param object|string $objectOrClass
	 * @param string $propertyName
	 * @return \ReflectionProperty
	 * @throws \ReflectionException
	 */
	public static function asPropertyRef($objectOrClass, $propertyName)
	{
		$property = new \ReflectionProperty($objectOrClass, $propertyName);
		/** @noinspection PhpExpressionResultUnusedInspection */
		$property->setAccessible(true);

		return $property;
	}

	/**
	 * 转换为 ReflectionParameter
	 * @param string|array|object $function
	 * @param int|string $parameterName
	 * @return \ReflectionParameter
	 * @throws \ReflectionException
	 */
	public static function asParameterRef($function, $parameterName)
	{
		return new \ReflectionParameter($function, $parameterName);
	}

	/**
	 * 获取类中指定的方法
	 * @param string|object|callable $objectOrClass
	 * @param string|null $methodName
	 * @param bool $throw
	 * @return \ReflectionMethod|\ReflectionFunction|null
	 * @throws \ReflectionException
	 */
	public static function method($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			if (is_callable($objectOrClass)) {
				return self::asFunctionRef($objectOrClass);
			}

			return self::asMethodRef($objectOrClass, $methodName);
		}, null, $throw);
	}

	/**
	 * 方法可见范围 - 无异常模式
	 *
	 * @param string|object $objectOrClass
	 * @param string $methodName
	 * @param bool $throw
	 * @return int
	 * @throws \ReflectionException
	 * @deprecated
	 * @see self::methodModifiers()
	 */
	public static function methodVisible($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			$ref = new \ReflectionMethod($objectOrClass, $methodName);
			if ($ref->isPublic()) {
				return self::VISIBLE_PUBLIC;
			}

			if ($ref->isProtected()) {
				return self::VISIBLE_PROTECTED;
			}

			return self::VISIBLE_PRIVATE;
		}, self::VISIBLE_PRIVATE, $throw);
	}

	/**
	 * 获取类方法可见范围
	 *
	 * @param mixed $objectOrClass
	 * @param string $methodName
	 * @return int
	 * @throws \ReflectionException
	 * @deprecated
	 * @see self::methodModifiers()
	 */
	public static function getMethodVisible($objectOrClass, $methodName, $throw = true)
	{
		return self::methodVisible($objectOrClass, $methodName, $throw);
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
		return self::throwIf(function () use ($callback) {
			if (is_array($callback)) {
				// 对于数组形式的回调，例如 [$object, 'methodName']
				$reflection = new \ReflectionMethod($callback[0], $callback[1]);
			} else {
				// 对于普通函数
				$reflection = new \ReflectionFunction($callback);
			}
			return $reflection->isGenerator();
		}, false, $throw);
	}

	/**
	 * 是否是公共方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function isPublicMethod($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isPublic();
		}, false, $throw);
	}

	/**
	 * 是否是保护方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function isProtectedMethod($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isProtected();
		}, false, $throw);
	}

	/**
	 * 是否是私有方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function isPrivateMethod($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isPrivate();
		}, false, $throw);
	}

	/**
	 * 是否是静态方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function isStaticMethod($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isStatic();
		}, false, $throw);
	}

	/**
	 * 是否是抽象方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function isAbstractMethod($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isAbstract();
		}, false, $throw);
	}

	/**
	 * 是否是构造方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function isConstructorMethod($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isConstructor();
		}, false, $throw);
	}

	/**
	 * 是否是析构方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function isDestructorMethod($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isDestructor();
		}, false, $throw);
	}

	/**
	 * 是否是闭包方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function isClosureMethod($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isClosure();
		}, false, $throw);
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
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @param bool $throw
	 * @return int
	 * @throws \ReflectionException
	 */
	public static function methodModifiers($objectOrClass, $methodName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			$methodName = self::method($objectOrClass, $methodName, true);
			return $methodName->getModifiers();
		}, \ReflectionMethod::IS_PRIVATE, $throw);
	}

	/**
	 * 获取类的方法
	 * @param string|object $objectOrClass
	 * @param bool $throw
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function methods($objectOrClass, $filter = null, $default = [], $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $filter) {
			$reflectionClass = new \ReflectionClass($objectOrClass);
			return $reflectionClass->getMethods($filter);
		}, $default, $throw);
	}

	/**
	 * 获取类或对象的公共方法
	 * @param string|object $objectOrClass
	 * @param bool $throw
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function publicMethods($objectOrClass, $default = [], $throw = false)
	{
		return self::methods($objectOrClass, \ReflectionMethod::IS_PUBLIC, $default, $throw);
	}

	/**
	 * 获取类或对象的受保护方法
	 * @param string|object $objectOrClass
	 * @param bool $throw
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function protectedMethods($objectOrClass, $default = [], $throw = false)
	{
		return self::methods($objectOrClass, \ReflectionMethod::IS_PROTECTED, $default, $throw);
	}

	/**
	 * 获取类或对象的私有方法
	 * @param string|object $objectOrClass
	 * @param bool $throw
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function privateMethods($objectOrClass, $default = [], $throw = false)
	{
		return self::methods($objectOrClass, \ReflectionMethod::IS_PRIVATE, $default, $throw);
	}

	/**
	 * 获取类或对象的终态方法
	 * @param string|object $objectOrClass
	 * @param bool $throw
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function finalMethods($objectOrClass, $default = [], $throw = false)
	{
		return self::methods($objectOrClass, \ReflectionMethod::IS_FINAL, $default, $throw);
	}

	/**
	 * 获取类或对象的抽象方法
	 * @param string|object $objectOrClass
	 * @param bool $throw
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function abstractMethods($objectOrClass, $default = [], $throw = false)
	{
		return self::methods($objectOrClass, \ReflectionMethod::IS_ABSTRACT, $default, $throw);
	}

	/**
	 * 获取类或对象的静态方法
	 * @param string|object $objectOrClass
	 * @param bool $throw
	 * @return \ReflectionMethod[]
	 * @throws \ReflectionException
	 */
	public static function staticMethods($objectOrClass, $default = [], $throw = false)
	{
		return self::methods($objectOrClass, \ReflectionMethod::IS_STATIC, $default, $throw);
	}

	/**
	 * 获取类属性
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @return \ReflectionProperty
	 * @throws \ReflectionException
	 * @deprecated
	 * @see self::property()
	 */
	public static function getProperty($objectOrClass, $propertyName)
	{
		return self::property($objectOrClass, $propertyName, true);
	}

	/**
	 * 获取类属性
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param bool $throw
	 * @return \ReflectionProperty
	 * @throws \ReflectionException
	 */
	public static function property($objectOrClass, $propertyName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName) {
			return self::asPropertyRef($objectOrClass, $propertyName);
		}, null, $throw);
	}

	/**
	 * 获取类属性值
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param mixed|null $default
	 * @param bool $throw
	 * @return mixed|null
	 * @throws \ReflectionException
	 */
	public static function get($objectOrClass, $propertyName, $default = null, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName) {
			return self::property($objectOrClass, $propertyName, true)->getValue(is_object($objectOrClass) ? $objectOrClass : null);
		}, $default, $throw);
	}

	/**
	 * 获取属性值
	 * @param object $classInstance
	 * @param string $property
	 * @param mixed|null $default
	 * @return mixed
	 * @throws \ReflectionException
	 * @deprecated
	 * @see self::get()
	 */
	public static function propertyValue($classInstance, $property, $default = null)
	{
		return self::get($classInstance, $property, $default, false);
	}

	/**
	 * 获取属性值
	 * @param object $classInstance
	 * @param string $property
	 * @return mixed
	 * @noinspection PhpDocMissingThrowsInspection
	 * @deprecated
	 * @see          self::get()
	 */
	public static function getPropertyValue($classInstance, $property, $default = null, $throw = false)
	{
		return self::get($classInstance, $property, $default, $throw);
	}

	/**
	 * 属性是否存在
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param bool $throw
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function has($objectOrClass, $propertyName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName) {
			return self::asClassRef($objectOrClass)->hasProperty($propertyName);
		}, false, $throw);
	}

	/**
	 * 指定的类或对象中是否包含指定的静态属性
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param bool $throw
	 * @return mixed|null
	 * @throws \ReflectionException
	 */
	public static function staticHas($objectOrClass, $propertyName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName) {
			if (!self::has($objectOrClass, $propertyName)) {
				return false;
			}

			return self::property($objectOrClass, $propertyName, true)->isStatic();
		}, false, $throw);
	}

	/**
	 * 指定的类或对象中是否包含指定的动态成员属性
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param bool $throw
	 * @return mixed|null
	 * @throws \ReflectionException
	 */
	public static function dynamicHas($objectOrClass, $propertyName, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName) {
			if (!self::has($objectOrClass, $propertyName)) {
				return false;
			}

			return !self::property($objectOrClass, $propertyName, true)->isStatic();
		}, false, $throw);
	}

	/**
	 * 指定的类或对象中是否包含指定的动态成员属性
	 * @param string|object $objectOrClass
	 * @return bool
	 * @throws \ReflectionException
	 * @deprecated
	 */
	public static function hasDynamicProperty($objectOrClass, $propertyName)
	{
		return self::dynamicHas($objectOrClass, $propertyName, false);
	}

	/**
	 * 属性是否存在
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param bool|null $isStatic 是否检测静态成员属性：true检测静态成员属性，false排除静态成员属性，null检测所有的成员属性
	 * @return bool
	 * @throws \ReflectionException
	 * @deprecated
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
	 * 指定的类或对象中是否包含指定的静态属性
	 * @param string|object $objectOrClass
	 * @return bool
	 * @throws \ReflectionException
	 * @deprecated
	 */
	public static function hasStaticProperty($objectOrClass, $propertyName)
	{
		return self::staticHas($objectOrClass, $propertyName, false);
	}

	/**
	 * 设置属性值
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param mixed $value
	 * @param bool $throw
	 * @return \ReflectionProperty
	 * @throws \ReflectionException
	 */
	public static function set($objectOrClass, $propertyName, $value, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName, $value) {
			$ref = self::property($objectOrClass, $propertyName);

			if (is_callable($value)) {
				$value = $value($ref->getValue());
			}

			if ($ref->isStatic()) {
				$ref->setValue($value);
			}

			$ref->setValue($objectOrClass, $value);
		}, null, $throw);
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
		return self::set($classInstance, $propertyName, $value, true);
	}

	/**
	 * 获取类属性列表
	 * @param string|object $objectOrClass
	 * @param int|null $filter
	 * @param bool $throw
	 * @return \ReflectionProperty
	 * @throws \ReflectionException
	 */
	public static function properties($objectOrClass, $filter = null, $default = [], $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $filter) {
			return self::asClassRef($objectOrClass)->getProperties($filter);
		}, $default, $throw);
	}

	/**
	 * 获取类或对象中是否包含的动态属性列表
	 * @param string|object $objectOrClass
	 * @return bool
	 * @throws \ReflectionException
	 * @deprecated
	 */
	public static function dynamicProperties($objectOrClass, $default = [], $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass) {
			return array_filter(self::asClassRef($objectOrClass)->getProperties(), function (\ReflectionProperty $property) {
				return !$property->isStatic();
			});
		}, $default, $throw);
	}

	/**
	 * 获取类或对象中是否包含的静态属性列表
	 * @param string|object $objectOrClass
	 * @return bool
	 * @throws \ReflectionException
	 * @deprecated
	 */
	public static function staticProperties($objectOrClass, $default = [], $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $default) {
			return self::asClassRef($objectOrClass)->getProperties(\ReflectionProperty::IS_STATIC);
		}, $default, $throw);
	}

	/**
	 * 回退试调用类方法
	 *
	 * @param string|object $objectOrClass
	 * @param array $methodNames
	 * @param array $args
	 * @return mixed
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function fallbackCalls($objectOrClass, array $methodNames, array $args = [])
	{
		foreach ($methodNames as $methodName) {
			if (\ReflectionMethod::IS_PUBLIC == self::methodModifiers($objectOrClass, $methodName)) {
				return call_user_func_array([$objectOrClass, $methodName], $args);
			}
		}

		return null;
	}

	/**
	 * 调用类方法
	 * @param string|object $objectOrClass
	 * @param string $methodName
	 * @param array $args
	 * @param bool $throw
	 * @return mixed|null
	 * @throws \ReflectionException
	 */
	public static function call($objectOrClass, $methodName, array $args = [], $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName, $args) {
			$methodRef = self::method($objectOrClass, $methodName);
			return $methodRef->invokeArgs($methodRef->isStatic() ? null : $objectOrClass, $args);
		}, null, $throw);
	}

	/**
	 * 获取父级
	 * @param string|object $objectOrClass
	 * @return false|\ReflectionClass
	 * @throws \ReflectionException
	 */
	public static function parent($objectOrClass, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass) {
			return self::asClassRef($objectOrClass)->getParentClass();
		}, false, $throw);
	}

	/**
	 * 获取父级列表
	 * @param string|object $objectOrClass
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function parents($objectOrClass, $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass) {
			$parents = [];
			$parent = self::parent($objectOrClass);

			while ($parent) {
				$parents[] = $parent;
				$parent = $parent->getParentClass();
			}

			return $parents;
		}, [], $throw);
	}

	/**
	 * 获取方法或函数的参数
	 * @param string|array|object $function
	 * @param int|string $parameterName
	 * @param bool $throw
	 * @return \ReflectionParameter|null
	 * @throws \ReflectionException
	 */
	public static function parameter($function, $parameterName, $throw = false)
	{
		return self::throwIf(function () use ($function, $parameterName) {
			return self::asParameterRef($function, $parameterName);
		}, null, $throw);
	}

	/**
	 * 获取方法或函数的参数类型
	 * @param string|array|object $function
	 * @param int|string $parameterName
	 * @param bool $throw
	 * @return \ReflectionParameter|null
	 * @throws \ReflectionException
	 */
	public static function parameterType($function, $parameterName, $throw = false)
	{
		return self::throwIf(function () use ($function, $parameterName) {
			self::parameter($function, $parameterName)->getType();
		}, null, $throw);
	}

	/**
	 * 获取方法或函数的参数位置
	 * @param string|array|object $function
	 * @param int|string $parameterName
	 * @param bool $throw
	 * @return \ReflectionParameter|null
	 * @throws \ReflectionException
	 */
	public static function parameterPosition($function, $parameterName, $throw = false)
	{
		return self::throwIf(function () use ($function, $parameterName) {
			self::parameter($function, $parameterName)->getPosition();
		}, -1, $throw);
	}

	/**
	 * 获取方法或函数的参数是否存在
	 * @param string|array|object $function
	 * @param int|string $parameterName
	 * @param bool $throw
	 * @return \ReflectionParameter|null
	 * @throws \ReflectionException
	 */
	public static function hasParameter($function, $parameterName, $throw = false)
	{
		return self::throwIf(function () use ($function, $parameterName) {
			$parameters = self::method($function, null)->getParameters();
			foreach ($parameters as $parameter) {
				if ($parameter->getName() == $parameterName) {
					return true;
				}
			}
			return false;
		}, -1, $throw);
	}
}
