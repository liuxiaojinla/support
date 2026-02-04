<?php

namespace Xin\Support;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

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
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	protected static function throwIf(callable $callback, $default = null, bool $throw = false)
	{
		try {
			return $callback();
		} catch (ReflectionException $e) {
			if ($throw) {
				/** @noinspection PhpUnhandledExceptionInspection */
				throw $e;
			}
		}

		return value($default);
	}

	/**
	 * 获取类中指定的方法
	 * @param string|object|callable $objectOrClass
	 * @param string|null $methodName
	 * @param bool $throw
	 * @return ReflectionMethod|ReflectionFunction|null
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function method($objectOrClass, ?string $methodName = null, bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			if (is_callable($objectOrClass)) {
				/** @noinspection PhpUnhandledExceptionInspection */
				return new ReflectionFunction($objectOrClass);
			}

			/** @noinspection PhpUnhandledExceptionInspection */
			$ref = new ReflectionMethod($objectOrClass, $methodName);
			/** @noinspection PhpExpressionResultUnusedInspection */
			$ref->setAccessible(true);

			return $ref;
		}, null, $throw);
	}

	/**
	 * 是否是生成器函数
	 * @param callable $callback
	 * @param bool $throw
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function isGenerator(callable $callback, bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($callback) {
			if (is_array($callback)) {
				// 对于数组形式的回调，例如 [$object, 'methodName']
				$reflection = new ReflectionMethod($callback[0], $callback[1]);
			} else {
				// 对于普通函数
				$reflection = new ReflectionFunction($callback);
			}
			return $reflection->isGenerator();
		}, false, $throw);
	}

	/**
	 * 是否是公共方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function isPublicMethod($objectOrClass, string $methodName, bool$throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isPublic();
		}, false, $throw);
	}

	/**
	 * 是否是保护方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function isProtectedMethod($objectOrClass, string $methodName, bool$throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isProtected();
		}, false, $throw);
	}

	/**
	 * 是否是私有方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function isPrivateMethod($objectOrClass, string $methodName, bool$throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isPrivate();
		}, false, $throw);
	}

	/**
	 * 是否是静态方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function isStaticMethod($objectOrClass, string $methodName, bool$throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isStatic();
		}, false, $throw);
	}

	/**
	 * 是否是抽象方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function isAbstractMethod($objectOrClass, string $methodName, bool$throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isAbstract();
		}, false, $throw);
	}

	/**
	 * 是否是构造方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function isConstructorMethod($objectOrClass, string $methodName, bool$throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isConstructor();
		}, false, $throw);
	}

	/**
	 * 是否是析构方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function isDestructorMethod($objectOrClass, string $methodName, bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			return self::method($objectOrClass, $methodName)->isDestructor();
		}, false, $throw);
	}

	/**
	 * 是否是闭包方法
	 * @param string|object $objectOrClass
	 * @param string|null $methodName
	 * @param bool $throw
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function isClosureMethod($objectOrClass, ?string $methodName = null, bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
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
	 * @param string $methodName
	 * @param bool $throw
	 * @return int
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function methodModifiers($objectOrClass, string $methodName, bool $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName) {
			$methodName = self::method($objectOrClass, $methodName, true);
			return $methodName->getModifiers();
		}, ReflectionMethod::IS_PRIVATE, $throw);
	}

	/**
	 * 获取类的方法
	 * @param string|object $objectOrClass
	 * @param int|null $filter
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionMethod[]
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function methods($objectOrClass, ?int $filter = null, array $default = [], bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $filter) {
			$reflectionClass = new ReflectionClass($objectOrClass);
			return $reflectionClass->getMethods($filter);
		}, $default, $throw);
	}

	/**
	 * 获取类或对象的公共方法
	 * @param string|object $objectOrClass
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionMethod[]
	 */
	public static function publicMethods($objectOrClass, array $default = [], bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::methods($objectOrClass, ReflectionMethod::IS_PUBLIC, $default, $throw);
	}

	/**
	 * 获取类或对象的受保护方法
	 * @param string|object $objectOrClass
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionMethod[]
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function protectedMethods($objectOrClass, array $default = [], bool $throw = false)
	{
		return self::methods($objectOrClass, ReflectionMethod::IS_PROTECTED, $default, $throw);
	}

	/**
	 * 获取类或对象的私有方法
	 * @param string|object $objectOrClass
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionMethod[]
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function privateMethods($objectOrClass, array $default = [], bool $throw = false)
	{
		return self::methods($objectOrClass, ReflectionMethod::IS_PRIVATE, $default, $throw);
	}

	/**
	 * 获取类或对象的终态方法
	 * @param string|object $objectOrClass
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionMethod[]
	 */
	public static function finalMethods($objectOrClass, array $default = [], bool $throw = false)
	{
		return self::methods($objectOrClass, ReflectionMethod::IS_FINAL, $default, $throw);
	}

	/**
	 * 获取类或对象的抽象方法
	 * @param string|object $objectOrClass
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionMethod[]
	 */
	public static function abstractMethods($objectOrClass, array $default = [], bool $throw = false)
	{
		return self::methods($objectOrClass, ReflectionMethod::IS_ABSTRACT, $default, $throw);
	}

	/**
	 * 获取类或对象的静态方法
	 * @param string|object $objectOrClass
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionMethod[]
	 */
	public static function staticMethods($objectOrClass, array $default = [], bool $throw = false)
	{
		return self::methods($objectOrClass, ReflectionMethod::IS_STATIC, $default, $throw);
	}

	/**
	 * 获取类属性
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param bool $throw
	 * @return ReflectionProperty
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function property($objectOrClass, string $propertyName, bool $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName) {
			/** @noinspection PhpUnhandledExceptionInspection */
			$property = new ReflectionProperty($objectOrClass, $propertyName);

			/** @noinspection PhpExpressionResultUnusedInspection */
			$property->setAccessible(true);

			return $property;
		}, null, $throw);
	}

	/**
	 * 获取类属性值
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param mixed|null $default
	 * @param bool $throw
	 * @return mixed|null
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function get($objectOrClass, string $propertyName, $default = null, bool $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName) {
			$propertyRef = self::property($objectOrClass, $propertyName, true);

			// 类名：区分静态属性（获取当前值）
			if ($propertyRef->isStatic()) {
				// 静态属性：获取当前值（不是默认值！）
				return $propertyRef->getValue();
			}

			// 对象实例：获取运行时值
			if (is_object($objectOrClass)) {
				return $propertyRef->getValue($objectOrClass);
			}

			// 非静态属性：获取声明时的默认值
			if (PHP_VERSION_ID >= 80000) {
				// 如果是 PHP 版本 >= 8.0
				return $propertyRef->getDefaultValue();
			} else {
				// PHP 7.x 兼容
				$defaultProperties = (new ReflectionClass($objectOrClass))->getDefaultProperties();
				if (!array_key_exists($propertyName, $defaultProperties)) {
					throw new ReflectionException("Property $propertyName does not exist.");
				}

				return $defaultProperties[$propertyName];
			}
		}, $default, $throw);
	}

	/**
	 * 属性是否存在
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param bool $throw
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function has($objectOrClass, string $propertyName, bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $propertyName) {
			/** @noinspection PhpUnhandledExceptionInspection */
			return (new ReflectionClass($objectOrClass))->hasProperty($propertyName);
		}, false, $throw);
	}

	/**
	 * 指定的类或对象中是否包含指定的静态属性
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param bool $throw
	 * @return mixed|null
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function hasStaticProperty($objectOrClass, string $propertyName, bool $throw = false)
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function hasDynamicProperty($objectOrClass, string $propertyName, bool $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName) {
			if (!self::has($objectOrClass, $propertyName)) {
				return false;
			}

			return !self::property($objectOrClass, $propertyName, true)->isStatic();
		}, false, $throw);
	}

	/**
	 * 设置属性值
	 * @param string|object $objectOrClass
	 * @param string $propertyName
	 * @param mixed $value
	 * @param bool $throw
	 * @return ReflectionProperty
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function set($objectOrClass, string $propertyName, $value, bool $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $propertyName, $value) {
			$ref = self::property($objectOrClass, $propertyName);

			if (is_callable($value)) {
				$value = $value($ref->getValue($ref->isStatic() ? null : $objectOrClass));
			}

			if ($ref->isStatic()) {
				$ref->setValue($value);
			} else {
				$ref->setValue($objectOrClass, $value);
			}
		}, null, $throw);
	}

	/**
	 * 获取类属性列表
	 * @param string|object $objectOrClass
	 * @param int|null $filter
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionProperty[]
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function properties($objectOrClass, ?int $filter = null, array $default = [], bool $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $filter) {
			$results = (new ReflectionClass($objectOrClass))->getProperties($filter);
			if (is_callable($filter)) {
				return array_filter($results, $filter);
			}

			return $results;
		}, $default, $throw);
	}

	/**
	 * 获取类或对象中是否包含的动态属性列表
	 * @param string|object $objectOrClass
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionProperty[]
	 */
	public static function dynamicProperties($objectOrClass, array $default = [], bool $throw = false)
	{
		$properties = self::properties($objectOrClass, ReflectionProperty::IS_STATIC, $default, $throw);
		return array_filter($properties, function ($p) {
			return !$p->isStatic();
		});
	}

	/**
	 * 获取类或对象中是否包含的静态属性列表
	 * @param string|object $objectOrClass
	 * @param array $default
	 * @param bool $throw
	 * @return ReflectionProperty[]
	 */
	public static function staticProperties($objectOrClass, array $default = [], bool $throw = false)
	{
		return self::properties($objectOrClass, ReflectionProperty::IS_STATIC, $default, $throw);
	}

	/**
	 * 回退试调用类方法
	 *
	 * @param string|object $objectOrClass
	 * @param array $methodNames
	 * @param array $args
	 * @return mixed
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function fallbackCalls($objectOrClass, array $methodNames, array $args = [])
	{
		foreach ($methodNames as $methodName) {
			if (ReflectionMethod::IS_PUBLIC == self::methodModifiers($objectOrClass, $methodName)) {
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function call($objectOrClass, string $methodName, array $args = [], bool $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass, $methodName, $args) {
			$methodRef = self::method($objectOrClass, $methodName);
			return $methodRef->invokeArgs($methodRef->isStatic() ? null : $objectOrClass, $args);
		}, null, $throw);
	}

	/**
	 * 获取父级
	 * @param string|object $objectOrClass
	 * @param bool $throw
	 * @return false|ReflectionClass
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function parent($objectOrClass, bool $throw = false)
	{
		return self::throwIf(function () use ($objectOrClass) {
			/** @noinspection PhpUnhandledExceptionInspection */
			return (new ReflectionClass($objectOrClass))->getParentClass();
		}, false, $throw);
	}

	/**
	 * 获取父级列表
	 * @param string|object $objectOrClass
	 * @param bool $throw
	 * @return ReflectionClass[]
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function parents($objectOrClass, bool $throw = false)
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
	 * @return ReflectionParameter|null
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function parameter($function, $parameterName, bool $throw = false)
	{
		return self::throwIf(function () use ($function, $parameterName) {
			/** @noinspection PhpUnhandledExceptionInspection */
			return new ReflectionParameter($function, $parameterName);
		}, null, $throw);
	}

	/**
	 * 获取方法或函数的参数类型
	 * @param string|array|object $function
	 * @param int|string $parameterName
	 * @param bool $throw
	 * @return ReflectionParameter|null
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function parameterType($function, $parameterName, bool $throw = false)
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
	 * @return ReflectionParameter|null
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function parameterPosition($function, $parameterName, bool $throw = false)
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
	 * @return ReflectionParameter|null
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function hasParameter($function, $parameterName, bool $throw = false)
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

	/**
	 * 获取类的常量
	 * @param class-string|object $objectOrClass
	 * @param int|null $filter
	 * @return array
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function constants($objectOrClass, ?int $filter = null, bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $filter) {
			return (new ReflectionClass($objectOrClass))->getConstants($filter);
		}, [], $throw);
	}

	/**
	 * 获取特定常量
	 * @param class-string|object $objectOrClass
	 * @param string $name
	 * @param bool $throw
	 * @return false|mixed
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function constant($objectOrClass, string $name, bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($name) {
			return (new ReflectionClass('MyClass'))->getConstant($name);
		}, null, $throw);
	}

	/**
	 * 检查常量是否存在
	 * @param class-string|object $objectOrClass
	 * @param string $name
	 * @param bool $throw
	 * @return bool
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function hasConstant($objectOrClass, string $name, bool $throw = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return self::throwIf(function () use ($objectOrClass, $name) {
			return (new ReflectionClass($objectOrClass))->hasConstant($name);
		}, false, $throw);
	}
}
