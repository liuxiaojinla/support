<?php

namespace Xin\Support\Proxy;

use BadMethodCallException;
use Closure;
use Xin\Support\Traits\Macroable;

/**
 * 微代理器
 */
class MacroProxy
{

	use Macroable;

	/**
	 * 正在点击的目标。
	 *
	 * @var mixed
	 */
	protected $target;

	/**
	 * 创建新的分流代理实例。
	 *
	 * @param mixed $target
	 * @return void
	 */
	public function __construct($target)
	{
		$this->target = $target;
	}

	/**
	 * 动态处理对类的调用。
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 * @throws BadMethodCallException
	 */
	public function __call(string $method, array $parameters)
	{
		if (!static::hasMacro($method)) {
			throw new BadMethodCallException(sprintf(
				'Method %s::%s does not exist.', static::class, $method
			));
		}

		$macro = static::$macros[$method];

		if ($macro instanceof Closure) {
			return call_user_func_array($macro->bindTo($this->target, get_class($this->target)), $parameters);
		}

		return $macro(...$parameters);
	}

}
