<?php

namespace Xin\Support;

use Hyperf\Context\ApplicationContext as HyperfApplicationContext;
use Hyperf\Di\Container as HyperfContainer;
use Illuminate\Support\Facades\Facade as IlluminateFacade;
use think\Facade as ThinkFacade;

if (class_exists(IlluminateFacade::class)) { // Laravel
	abstract class Facade extends IlluminateFacade
	{
	}
} elseif (class_exists(ThinkFacade::class)) { // ThinkPHP
	abstract class Facade extends ThinkFacade
	{
	}
} elseif (class_exists(HyperfApplicationContext::class)) { // Hyperf
	abstract class Facade
	{
		/**
		 * @param string $name
		 * @param array $arguments
		 * @return mixed
		 */
		public static function __callStatic(string $name, array $arguments)
		{
			/** @var HyperfContainer $container */
			$container = HyperfApplicationContext::getContainer();
			$hint = $container->get(static::getFacadeAccessor());
			return call_user_func_array([$hint, $name], $arguments);
		}
	}
} else { // 其他框架
	class Facade
	{
		/**
		 * @var mixed
		 */
		protected static $instance;

		/**
		 * @var callable
		 */
		protected static $facadeAccessorResolver;

		/**
		 * @param callable $facadeAccessorResolver
		 * @return void
		 */
		public static function setFacadeAccessorResolver(callable $facadeAccessorResolver)
		{
			static::$facadeAccessorResolver = $facadeAccessorResolver;
		}

		/**
		 * 设置实例
		 * @param mixed $instance
		 * @return void
		 */
		public static function setInstance($instance)
		{
			static::$instance = $instance;
		}

		/**
		 * @return mixed
		 */
		protected static function instance()
		{
			if (self::$instance === null) {
				self::$instance = call_user_func(static::getFacadeAccessorResolver());
			}

			return self::$instance;
		}

		/**
		 * @param string $name
		 * @param array $arguments
		 * @return mixed
		 */
		public static function __callStatic($name, $arguments)
		{
			return call_user_func_array([self::instance(), $name], $arguments);
		}
	}
}
