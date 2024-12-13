<?php

namespace Xin\Support;

use Symfony\Component\Process\Process as SymfonyProcess;

final class Process
{
	/**
	 * 使用当前进程进行执行进程（exec）
	 * @param array $commands
	 * @param callable $processHandlerCallback
	 * @param callable|null $makeProcessCallback
	 * @param array $options
	 * @return SymfonyProcess
	 */
	public static function run(array $commands, callable $processHandlerCallback, callable $makeProcessCallback = null, array $options = []): SymfonyProcess
	{
		$process = self::make($commands, $options);

		return self::runProcess($process, $processHandlerCallback, $makeProcessCallback);
	}

	/**
	 * 生成进程实例
	 * @param array $commands
	 * @param array $options
	 * @return SymfonyProcess
	 */
	public static function make(array $commands, array $options = [])
	{
		$options = self::pretreatmentOptions($options);
		return new SymfonyProcess($commands, $options['cwd'], $options['env'], $options['input'], $options['timeout']);
	}

	/**
	 * 预处理参数
	 * @param array $options
	 * @return array
	 */
	public static function pretreatmentOptions(array $options = [])
	{
		return array_merge([
			'cwd'     => null,
			'env'     => null,
			'input'   => null,
			'timeout' => 0,
		], $options);
	}

	/**
	 * 执行进程
	 * @param SymfonyProcess $process
	 * @param callable $processHandlerCallback
	 * @param callable|null $makeProcessCallback
	 * @return SymfonyProcess
	 */
	protected static function runProcess(SymfonyProcess $process, callable $processHandlerCallback, callable $makeProcessCallback = null)
	{
		if ($makeProcessCallback) {
			call_user_func($makeProcessCallback, $process);
		}

		$process->run($processHandlerCallback);

		return $process;
	}

	/**
	 * 根据命令行执行进程
	 * @param string $command
	 * @param callable $processHandlerCallback
	 * @param callable|null $makeProcessCallback
	 * @param array $options
	 * @return SymfonyProcess
	 */
	public static function runShellCommandline(string $command, callable $processHandlerCallback, callable $makeProcessCallback = null, array $options = [])
	{
		$process = self::fromShellCommandline($command, $options);

		return self::runProcess($process, $processHandlerCallback, $makeProcessCallback);
	}

	/**
	 * 根据命令行生成进程实例
	 * @param string $command
	 * @param array $options
	 * @return SymfonyProcess
	 */
	public static function fromShellCommandline(string $command, array $options = [])
	{
		$options = self::pretreatmentOptions($options);
		return SymfonyProcess::fromShellCommandline($command, $options['cwd'], $options['env'], $options['input'], $options['timeout']);
	}
}
