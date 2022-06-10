<?php

namespace Xin\Support;

use Xin\Support\Traits\Macroable;

class ClassMapManager implements \ArrayAccess
{

	use Macroable;

	/**
	 * @var array
	 */
	protected $maps = [];

	/**
	 * 获取类型列表
	 *
	 * @return array
	 */
	public function getMaps()
	{
		return $this->maps;
	}

	/**
	 * 获取所有的类型
	 * @return string[]
	 */
	public function getTypes()
	{
		return array_keys($this->maps);
	}

	/**
	 * 获取所有映射的类
	 * @return array
	 */
	public function getClassList()
	{
		return array_values($this->maps);
	}

	/**
	 * 绑定关联类型
	 *
	 * @param string $type
	 * @param string $class
	 */
	public function bind($type, $class)
	{
		if ($this->has($type)) {
			throw new \LogicException("class map {$type} duplicate defined.");
		}

		$this->maps[$type] = $class;
	}

	/**
	 * 判断类型是否存在
	 *
	 * @param string $type
	 * @return bool
	 */
	public function has($type)
	{
		return isset($this->maps[$type]);
	}

	/**
	 * 获取类型指定的类
	 *
	 * @param string $type
	 * @return string
	 */
	public function get($type)
	{
		if (!$this->has($type)) {
			throw new \LogicException("class map {$type} not defined.");
		}

		return $this->maps[$type];
	}

	/**
	 * 移除类型映射
	 * @param string $type
	 * @return void
	 */
	public function forget($type)
	{
		unset($this->maps[$type]);
	}

	/**
	 * 清除类型映射
	 * @return void
	 */
	public function clear()
	{
		$this->maps = [];
	}

	/**
	 * 调用对应关联资源的方法
	 *
	 * @param string $type
	 * @param string $method
	 * @param array $args
	 * @return false|mixed
	 */
	public function call($type, $method, $args = [])
	{
		$class = $this->get($type);

		if (!method_exists($class, $method)) {
			return null;
		}

		return call_user_func_array([$class, $method], $args);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value)
	{
		$this->bind($offset, $value);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset)
	{
		$this->forget($offset);
	}

}
