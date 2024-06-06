<?php

namespace Xin\Support;

use Hyperf\Context\ApplicationContext as HyperfApplicationContext;
use Illuminate\Container\Container as LaravelContainer;
use Illuminate\Foundation\Application as LaravelApp;
use RuntimeException;
use Support\Container as WebmanContainer;
use think\App as ThinkApp;
use think\Container as ThinkContainer;
use Webman\App as WebmanApp;

final class Framework
{
    /**
     * 是否是Hyperf框架.
     * @return bool
     */
    public static function isHyperf()
    {
        return class_exists(HyperfApplicationContext::class);
    }

    /**
     * 是否是Webman框架.
     * @return bool
     */
    public static function isWebman()
    {
        return class_exists(WebmanApp::class);
    }

    /**
     * 是否是ThinkPHP框架.
     * @return bool
     */
    public static function isThinkPHP()
    {
        return class_exists(ThinkApp::class);
    }

    /**
     * 是否是Laravel框架.
     * @return bool
     */
    public static function isLaravel()
    {
        return class_exists(LaravelApp::class);
    }

    /**
     * 获取框架的容器.
     * @return \Psr\Container\ContainerInterface
     */
    public static function container()
    {
        if (self::isLaravel()) {
            return LaravelContainer::getInstance();
        } elseif (self::isThinkPHP()) {
            return ThinkContainer::getInstance();
        } elseif (self::isHyperf()) {
            return HyperfApplicationContext::getContainer();
        } elseif (self::isWebman()) {
            return WebmanContainer::instance();
        }

        self::throwUnknownFrameworkException();
    }

    /**
     * @throws RuntimeException
     */
    protected static function throwUnknownFrameworkException()
    {
        throw new RuntimeException('Unknown framework.');
    }

    /**
     * @return FrameworkInvoke
     */
    public static function when()
    {
        return new FrameworkInvoke();
    }
}

class FrameworkInvoke
{
    /**
     * @var array
     */
    protected $things = [];

    /**
     * @var callable
     */
    protected $otherwise;

    /**
     * FrameworkInvoke constructor.
     */
    public function __construct()
    {
        $this->otherwise(function () {
            return null;
        });
    }

    /**
     * @param callable $callback
     * @param callable|null $otherwise
     * @return $this
     */
    public function laravel(callable $callback, callable $otherwise = null)
    {
        return $this->when(Framework::isLaravel(), $callback, $otherwise);
    }

    /**
     * @param callable $callback
     * @param callable|null $otherwise
     * @return $this
     */
    public function thinkphp(callable $callback, callable $otherwise = null)
    {
        return $this->when(Framework::isThinkPHP(), $callback, $otherwise);
    }

    /**
     * @param callable $callback
     * @param callable|null $otherwise
     * @return $this
     */
    public function webman(callable $callback, callable $otherwise = null)
    {
        return $this->when(Framework::isWebman(), $callback, $otherwise);
    }

    /**
     * @param callable $callback
     * @param callable|null $otherwise
     * @return $this
     */
    public function hyperf(callable $callback, callable $otherwise = null)
    {
        return $this->when(Framework::isHyperf(), $callback, $otherwise);
    }

    /**
     * @param bool $condition
     * @param callable $callback
     * @param callable|null $otherwise
     * @return $this
     */
    public function when($condition, callable $callback, callable $otherwise = null)
    {
        $this->things[] = [(bool)$condition, $callback, $otherwise];

        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function otherwise(callable $callback)
    {
        $this->otherwise = $callback;

        return $this;
    }

    public function invoke()
    {
        foreach ($this->things as $thing) {
            if ($thing[0]) {
                return call_user_func($thing[1]);
            } elseif ($thing[2]) {
                call_user_func($thing[1]);
            }
        }

        return call_user_func($this->otherwise);
    }
}
