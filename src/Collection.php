<?php

namespace Xin\Support;

class Collection extends Fluent implements \Countable, \IteratorAggregate
{

	/**
	 * 过滤器
	 * @param callable $callback
	 * @param int $mode
	 * @return $this
	 */
	public function filter($callback = null, $mode = 0)
	{
		return new static(array_filter($this->items, $callback, $mode));
	}

	/**
	 * 获取一列数据
	 * @param string $key
	 * @param string $index
	 * @return $this
	 */
	public function column($key = null, $index = null)
	{
		return new static(array_column($this->items, $key, $index));
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Retrieve an external iterator.
	 *
	 * @see http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \ArrayIterator An instance of an object implementing <b>Iterator</b> or
	 *                        <b>Traversable</b>
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Count elements of an object.
	 *
	 * @see http://php.net/manual/en/countable.count.php
	 * @return int the custom count as an integer.
	 *             </p>
	 *             <p>
	 *             The return value is cast to an integer
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * var_export.
	 *
	 * @return array
	 */
	public function __set_state()
	{
		return $this->all();
	}

}
