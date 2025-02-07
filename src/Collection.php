<?php

namespace Xin\Support;

class Collection extends Fluent implements \Countable, \IteratorAggregate
{

	/**
	 * 过滤器
	 * @param callable|null $callback
	 * @param int $mode
	 * @return $this
	 */
	public function filter(callable$callback = null, int$mode = 0)
	{
		return new static(array_filter($this->items, $callback, $mode));
	}

	/**
	 * 获取一列数据
	 * @param string|null $key
	 * @param string|null $index
	 * @return $this
	 */
	public function column(string$key = null, string$index = null)
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
	#[\ReturnTypeWillChange]
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
	#[\ReturnTypeWillChange]
	public function count()
	{
		return count($this->items);
	}

}
