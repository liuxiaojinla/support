<?php

namespace Xin\Support;

use ArrayAccess;
use JsonSerializable;
use Xin\Support\Contracts\Arrayable;

class Fluent implements ArrayAccess, JsonSerializable, Arrayable
{

	/**
	 * The collection data.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * set data.
	 *
	 * @param array $items
	 */
	public function __construct(array $items = [])
	{
		foreach ($items as $key => $value) {
			$this->set($key, $value);
		}
	}

	/**
	 * 创建
	 * @param array $items
	 * @param Fluent|null $instance
	 * @return static
	 */
	public static function make(array $items = [], ?Fluent $instance = null)
	{
		return new static($items);
	}

	/**
	 * Set the item value.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
		Arr::set($this->items, $key, $value);
	}

	/**
	 * Return specific items.
	 *
	 * @param array $keys
	 * @return $this
	 */
	public function only(array $keys)
	{
		$return = [];

		foreach ($keys as $key) {
			$value = $this->get($key);

			if (!is_null($value)) {
				$return[$key] = $value;
			}
		}

		return static::make($return, $this);
	}

	/**
	 * Retrieve item from Collection.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return Arr::get($this->items, $key, $default);
	}

	/**
	 * Get all items except for those with the specified keys.
	 *
	 * @param mixed $keys
	 * @return static
	 */
	public function except($keys)
	{
		$keys = is_array($keys) ? $keys : func_get_args();

		return static::make(Arr::except($this->items, $keys), $this);
	}

	/**
	 * Merge data.
	 *
	 * @param Collection|array $items
	 * @return $this
	 */
	public function merge($items)
	{
		$clone = static::make($this->all(), $this);

		foreach ($items as $key => $value) {
			$clone->set($key, $value);
		}

		return $clone;
	}

	/**
	 * Return all items.
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * Retrieve the first item.
	 *
	 * @return mixed
	 */
	public function first()
	{
		return reset($this->items);
	}

	/**
	 * Retrieve the last item.
	 *
	 * @return bool
	 */
	public function last()
	{
		$end = end($this->items);

		reset($this->items);

		return $end;
	}

	/**
	 * add the item value.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function add($key, $value)
	{
		Arr::set($this->items, $key, $value);
	}

	/**
	 * Build to array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->all();
	}

	/**
	 * To string.
	 *
	 * @return string
	 */
	#[\ReturnTypeWillChange]
	public function __toString()
	{
		return $this->toJson();
	}

	/**
	 * Build to json.
	 *
	 * @param int $option
	 * @return string
	 */
	public function toJson($option = JSON_UNESCAPED_UNICODE)
	{
		return json_encode($this->all(), $option);
	}

	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Specify data which should be serialized to JSON.
	 *
	 * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return array data which can be serialized by <b>json_encode</b>,
	 *               which is a value of any type other than a resource
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->items;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object.
	 *
	 * @see http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	#[\ReturnTypeWillChange]
	public function __serialize()
	{
		return serialize($this->items);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object.
	 *
	 * @see  http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 * @return mixed|void
	 */
	#[\ReturnTypeWillChange]
	public function __unserialize($serialized)
	{
		return $this->items = unserialize($serialized);
	}

	/**
	 * Get a data by key.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Assigns a value to the specified data.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Whether or not an data exists by key.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		return $this->has($key);
	}

	/**
	 * To determine Whether the specified element exists.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		return !is_null(Arr::get($this->items, $key));
	}

	/**
	 * Unset an data by key.
	 *
	 * @param string $key
	 */
	public function __unset($key)
	{
		$this->forget($key);
	}

	/**
	 * Remove item form Collection.
	 *
	 * @param string $key
	 */
	public function forget($key)
	{
		Arr::forget($this->items, $key);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset.
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset($offset)
	{
		if ($this->offsetExists($offset)) {
			$this->forget($offset);
		}
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists.
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 * @return bool true on success or false on failure.
	 *              The return value will be casted to boolean if non-boolean was returned
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve.
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 * @return mixed Can return all value types
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->offsetExists($offset) ? $this->get($offset) : null;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set.
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value <p>
	 *                      The value to set.
	 *                      </p>
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

}
