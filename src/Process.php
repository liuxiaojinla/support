<?php

namespace Xin\Support;

use Symfony\Component\Process\Process as SymfonyProcess;

final class Process
{
    /**
     * 生成进程实例
     * @param array $commands
     * @return SymfonyProcess
     */
    public static function make(array $commands)
    {
        $process = new SymfonyProcess($commands);
        $process->setTimeout(0);

        return $process;
    }

    /**
     * 进程执行
     * @param array $commands
     * @param callable $processHandlerCallback
     * @param callable|null $preMakeProcessCallback
     * @return SymfonyProcess
     */
    public static function run(array $commands, callable $processHandlerCallback, callable $preMakeProcessCallback = null): SymfonyProcess
    {
        $process = self::make($commands);

        if ($preMakeProcessCallback) {
            call_user_func($preMakeProcessCallback, $process);
        }

        $process->run($processHandlerCallback);

        return $process;
    }
}
