<?php

namespace Xin\Support\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @method static void log(...$data)
 * @method static void info(...$data)
 * @method static void error(...$data)
 * @method static void success(...$data)
 * @method static void warn(...$data)
 * @method static void json(...$data)
 * @method static void errorJson(...$data)
 * @method static void successJson(...$data)
 * @mixin SymfonyStyle
 */
class Printer
{
	use WithOutputStyleOfStaticClass;

	private static $consolePrinter;

	/**
	 * 设置前缀名称
	 * @param string $name
	 * @return void
	 */
	public static function setPrefixName(string $name)
	{
		self::getConsolePrinter()->useNamePrefix($name);
	}

	/**
	 * 获取控制台输出
	 * @return ConsolePrinter
	 */
	protected static function getConsolePrinter()
	{
		if (!self::$consolePrinter) {
			self::$consolePrinter = self::printer();
		}

		return self::$consolePrinter;
	}

	/**
	 * 设置控制台输出
	 * @param mixed $consolePrinter
	 */
	public static function setConsolePrinter(ConsolePrinter $consolePrinter)
	{
		self::$consolePrinter = $consolePrinter;
	}

	/**
	 * 创建控制台输出
	 * @param string|null $name
	 * @return ConsolePrinter
	 */
	public static function printer(string $name = null)
	{
		return new ConsolePrinter($name);
	}

	/**
	 * 静态方法代理
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		if (method_exists(self::getConsolePrinter(), $name)) {
			return call_user_func_array([self::getConsolePrinter(), $name], $arguments);
		}

		return call_user_func_array([self::outputStyle(), $name], $arguments);
	}
}
