<?php

namespace Xin\Support\Console;

use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

trait WithOutputStyle
{
	/**
	 * @var OutputStyle
	 */
	private $outputStyle;

	/**
	 * @return OutputInterface|StyleInterface
	 */
	public function outputStyle()
	{
		if ($this->outputStyle === null) {
			if (!class_exists(SymfonyStyle::class)) {
				throw new RuntimeException('You need to run "composer require symfony/console".');
			}

			$this->outputStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
		}

		return $this->outputStyle;
	}
}
