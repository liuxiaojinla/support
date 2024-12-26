<?php

namespace Xin\Support\Console;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

trait WithOutputStyleOfStaticClass
{
	/**
	 * @var OutputStyle
	 */
	private static $outputStyle;

	/**
	 * @return OutputInterface|StyleInterface
	 */
	public static function outputStyle()
	{
		if (self::$outputStyle === null) {
			if (!class_exists(SymfonyStyle::class)) {
				throw new \RuntimeException('You need to run "composer require symfony/console".');
			}

			self::$outputStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
		}

		return self::$outputStyle;
	}
}
