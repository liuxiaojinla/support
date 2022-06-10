<?php

namespace Xin\Support;

use Xin\Support\Traits\Macroable;

class MacroProxy
{

	use Macroable;

	/**
	 * The target being tapped.
	 *
	 * @var mixed
	 */
	protected $target;

	/**
	 * Create a new tap proxy instance.
	 *
	 * @param mixed $target
	 * @return void
	 */
	public function __construct($target)
	{
		$this->target = $target;
	}

	/**
	 * Dynamically handle calls to the class.
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $parameters)
	{
		if (!static::hasMacro($method)) {
			throw new \BadMethodCallException(sprintf(
				'Method %s::%s does not exist.', static::class, $method
			));
		}

		$macro = static::$macros[$method];

		if ($macro instanceof \Closure) {
			return call_user_func_array($macro->bindTo($this->target, get_class($this->target)), $parameters);
		}

		return $macro(...$parameters);
	}

}
