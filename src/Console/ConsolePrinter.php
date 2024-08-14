<?php

namespace Xin\Support\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @mixin SymfonyStyle
 */
class ConsolePrinter
{
    public const INFO_COLOR = "\033[34m";

    public const ERROR_COLOR = "\033[31m";

    public const SUCCESS_COLOR = "\033[32m";

    public const WARNING_COLOR = "\033[31m\033[33m";

    public const RESET_COLOR = "\033[0m";

    /**
     * @var callable
     */
    protected $prefixCallback;

    /**
     * @var bool
     */
    protected $showDateTime = true;

    /**
     * @param string $name
     */
    public function __construct($name = null)
    {
        if ($name) {
            $this->useNamePrefix($name);
        }
    }

    /**
     * 默认打印
     * @param mixed ...$data
     * @return void
     */
    protected function print($type, ...$data)
    {
        echo $this->getPrefixColor($type) . $this->getPrefix($type) . self::RESET_COLOR, " ";

        if ($this->isShowDateTime()) {
            echo now()->format("Y-m-d H:i:s"), " ";
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
     * @return void
     */
    public function endLine()
    {
        echo PHP_EOL;
    }

    /**
     * @return void
     */
    protected function endStyleLine()
    {
        echo "\033[0m", PHP_EOL;
    }

    /**
     * 打印普通消息
     * @param mixed ...$data
     * @return void
     */
    public function log(...$data)
    {
        $this->print(__FUNCTION__, ...$data);
        $this->endLine();
    }

    /**
     * 打印提示信息
     * @param mixed ...$data
     * @return void
     */
    public function info(...$data)
    {
        $this->print(__FUNCTION__, ...$data);
        $this->endLine();
    }

    /**
     * @param mixed $text
     * @return string
     */
    public static function infoText($text)
    {
        return self::INFO_COLOR . $text . self::RESET_COLOR;
    }

    /**
     * 打印错误信息
     * @param mixed ...$data
     * @return void
     */
    public function error(...$data)
    {
        $this->print(__FUNCTION__, ...$data);
        $this->endLine();
    }

    /**
     * @param mixed $text
     * @return string
     */
    public static function errorText($text)
    {
        return self::ERROR_COLOR . $text . self::RESET_COLOR;
    }

    /**
     * 打印警告信息
     * @param mixed ...$data
     * @return void
     */
    public function warning(...$data)
    {
        $this->print(__FUNCTION__, ...$data);
        $this->endLine();
    }

    /**
     * 打印警告信息
     * @param mixed ...$data
     * @return void
     */
    public function warn(...$data)
    {
        $this->warning(...$data);
    }

    /**
     * @param mixed $text
     * @return string
     */
    public static function warningText($text)
    {
        return self::WARNING_COLOR . $text . self::RESET_COLOR;
    }

    /**
     * 打印成功信息
     * @param mixed ...$data
     * @return void
     */
    public function success(...$data)
    {
        $this->print(__FUNCTION__, ...$data);
        $this->endLine();
    }

    /**
     * @param mixed $text
     * @return string
     */
    public function successText($text)
    {
        return self::SUCCESS_COLOR . $text . self::RESET_COLOR;
    }

    /**
     * 打印常规json类型信息
     * @param mixed ...$data
     * @return void
     */
    public function logJson(...$data)
    {
        $this->print('log', $data);
        echo PHP_EOL;
    }

    /**
     * 打印错误json类型信息
     * @param mixed ...$data
     * @return void
     */
    public function errorJson(...$data)
    {
        $this->error($data);
    }

    /**
     * 打印成功类型信息
     * @param mixed ...$data
     * @return void
     */
    public function successJson(...$data)
    {
        $this->success($data);
    }

    /**
     * @return callable
     */
    public function getPrefixResolver()
    {
        return $this->prefixCallback;
    }

    /**
     * 设置前缀解析器
     * @param callable $resolver
     * @return void
     */
    public function setPrefixResolver(callable $resolver)
    {
        $this->prefixCallback = $resolver;
    }

    /**
     * 获取前缀信息
     * @return string
     */
    public function getPrefix($type)
    {
        if (!$this->prefixCallback) {
            return "[$type]";
        }

        return call_user_func($this->prefixCallback, $type);
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getPrefixColor($type)
    {
        if ('info' === $type) {
            return self::INFO_COLOR;
        } elseif ('success' === $type) {
            return self::SUCCESS_COLOR;
        } elseif ('error' === $type) {
            return self::ERROR_COLOR;
        } elseif ('warning' === $type) {
            return self::WARNING_COLOR;
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isShowDateTime()
    {
        return $this->showDateTime;
    }

    /**
     * @param bool $showDateTime
     * @return void
     */
    public function setShowDateTime(bool $showDateTime)
    {
        $this->showDateTime = $showDateTime;
    }

    /**
     * @param string $name
     * @return void
     */
    public function useNamePrefix($name)
    {
        $this->setPrefixResolver(function ($type) use ($name) {
            return "[$name.$type]";
        });
    }
}
