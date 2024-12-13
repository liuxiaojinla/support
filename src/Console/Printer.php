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

	public static function setPrefixName($name)
	{
		self::getConsolePrinter()->useNamePrefix($name);
	}

	/**
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
	 * @param mixed $consolePrinter
	 */
	public static function setConsolePrinter(ConsolePrinter $consolePrinter)
	{
		self::$consolePrinter = $consolePrinter;
	}

	/**
	 * @param string $name
	 * @return ConsolePrinter
	 */
	public static function printer($name = null)
	{
		return new ConsolePrinter($name);
	}

	/**
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
