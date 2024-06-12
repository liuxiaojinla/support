<?php

namespace Xin\Support\Console;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @mixin SymfonyStyle
 */
class Printer
{
    /**
     * @var OutputStyle
     */
    private static $outputStyle;

    /**
     * @var callable
     */
    private static $prefixCallback;

    /**
     * 默认打印
     * @param mixed ...$data
     * @return void
     */
    public static function print(...$data)
    {
        if ($prefix = self::getPrefix()) {
            echo $prefix, "";
        }

        foreach ($data as $value) {
            if (is_scalar($value)) {
                echo $value;
            } else {
                echo json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            echo " ";
        }
    }

    /**
     * 打印普通消息
     * @param mixed ...$data
     * @return void
     */
    public static function println(...$data)
    {
        self::print(...$data);
        echo PHP_EOL;
    }

    /**
     * 打印提示信息
     * @param mixed ...$data
     * @return void
     */
    public static function info(...$data)
    {
        echo "\033[34m";
        self::print(...$data);
        self::endStyleLine();
    }

    /**
     * 打印错误信息
     * @param mixed ...$data
     * @return void
     */
    public static function error(...$data)
    {
        echo "\033[31m";
        self::print(...$data);
        self::endStyleLine();
    }

    /**
     * 打印成功信息
     * @param mixed ...$data
     * @return void
     */
    public static function success(...$data)
    {
        echo "\033[32m";
        self::print(...$data);
        self::endStyleLine();
    }

    /**
     * 打印警告信息
     * @param mixed ...$data
     * @return void
     */
    public static function warn(...$data)
    {
        echo "\033[31m\033[33m";
        self::print(...$data);
        self::endStyleLine();
    }

    /**
     * @return void
     */
    protected static function endStyleLine()
    {
        echo "\033[0m", PHP_EOL;
    }

    /**
     * 打印常规json类型信息
     * @param mixed ...$data
     * @return void
     */
    public static function json(...$data)
    {
        self::print($data);
        echo PHP_EOL;
    }

    /**
     * 打印错误json类型信息
     * @param mixed ...$data
     * @return void
     */
    public static function errorJson(...$data)
    {
        self::error($data);
    }

    /**
     * 打印成功类型信息
     * @param mixed ...$data
     * @return void
     */
    public static function successJson(...$data)
    {
        self::success($data);
    }

    /**
     * @return OutputInterface|StyleInterface
     */
    public static function outputStyle()
    {
        if (self::$outputStyle === null) {
            self::$outputStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        }

        return self::$outputStyle;
    }

    /**
     * 设置前缀解析器
     * @param callable $resolver
     * @return void
     */
    public static function setPrefixResolver(callable $resolver)
    {
        self::$prefixCallback = $resolver;
    }

    /**
     * 获取前缀信息
     * @return string
     */
    public static function getPrefix()
    {
        if (!self::$prefixCallback) {
            return "";
        }

        return call_user_func(self::$prefixCallback);
    }

    /**
     * @return void
     */
    public static function setPrefixWithDefault()
    {
        self::$prefixCallback = function () {
            return now()->format("Y-m-d H:i:s");
        };
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return call_user_func_array([self::outputStyle(), $name], $arguments);
    }
}
