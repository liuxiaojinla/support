<?php

namespace Xin\Support;

use ReturnTypeWillChange;
use Xin\Support\Traits\Macroable;

class ClassMapManager implements \ArrayAccess
{

	use Macroable;

	/**
	 * @var array
	 */
	protected $mapping = [];

	/**
	 * 获取类型列表
	 *
	 * @return array
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * 获取所有的类型
	 * @return string[]
	 */
	public function getTypes()
	{
		return array_keys($this->mapping);
	}

	/**
	 * 获取所有映射的类
	 * @return array
	 */
	public function getClassList()
	{
		return array_values($this->mapping);
	}

	/**
	 * 清除类型映射
	 * @return void
	 */
	public function clear()
	{
		$this->mapping = [];
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

		return $this->mapping[$type];
	}

	/**
	 * 判断类型是否存在
	 *
	 * @param string $type
	 * @return bool
	 */
	public function has($type)
	{
		return isset($this->mapping[$type]);
	}

	/**
	 * @inheritDoc
	 */
	#[ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	/**
	 * @inheritDoc
	 */
	#[ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * @inheritDoc
	 */
	#[ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		$this->bind($offset, $value);
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

		$this->mapping[$type] = $class;
	}

	/**
	 * @inheritDoc
	 */
	#[ReturnTypeWillChange]
	public function offsetUnset($offset)
	{
		$this->forget($offset);
	}

	/**
	 * 移除类型映射
	 * @param string $type
	 * @return void
	 */
	public function forget($type)
	{
		unset($this->mapping[$type]);
	}

}
