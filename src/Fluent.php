<?php
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Xin\Support;

use ArrayAccess;
use JsonSerializable;
use Xin\Support\Contracts\Arrayable;
use Xin\Support\Contracts\Jsonable;

class Fluent implements ArrayAccess, JsonSerializable, Arrayable, Jsonable
{

	/**
	 * 集合数据。
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * 设置数据。
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
	 * 创建实例
	 * @param array $items
	 * @param Fluent|null $instance
	 * @return static
	 */
	public static function make(array $items = [], ?Fluent $instance = null)
	{
		return new static($items);
	}

	/**
	 * 添加项值。
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function add($key, $value)
	{
		Arr::set($this->items, $key, $value);
	}

	/**
	 * 设置项值。
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
		Arr::set($this->items, $key, $value);
	}

	/**
	 * 返回指定键的项。
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
	 * 从集合中检索项。
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
	 * 获取除指定键之外的所有项。
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
	 * 合并数据。
	 *
	 * @param iterable|array $items
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
	 * 返回所有项。
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * 检索第一项。
	 *
	 * @return mixed
	 */
	public function first()
	{
		return reset($this->items);
	}

	/**
	 * 检索最后一项。
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
	 * 检查集合是否为空。
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		return empty($this->items);
	}

	/**
	 * 构建为数组。
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->all();
	}

	/**
	 * 转换为字符串。
	 *
	 * @return string
	 */
	#[\ReturnTypeWillChange]
	public function __toString()
	{
		return $this->toJson();
	}

	/**
	 * 构建为 JSON。
	 *
	 * @param int $flags
	 * @return string
	 */
	public function toJson(int $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	{
		return json_encode($this->all(), $flags);
	}

	/**
	 * 指定应序列化为 JSON 的数据。
	 *
	 * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return array 可由 <b>json_encode</b> 序列化的数据，即除资源类型外的任何类型的值
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->items;
	}

	/**
	 * 对象的字符串表示。
	 *
	 * @see http://php.net/manual/en/serializable.serialize.php
	 * @return string 对象的字符串表示或 null
	 */
	#[\ReturnTypeWillChange]
	public function __serialize()
	{
		return serialize($this->items);
	}

	/**
	 * 构造对象。
	 *
	 * @see  http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized 对象的字符串表示。
	 * @return mixed|void
	 */
	#[\ReturnTypeWillChange]
	public function __unserialize($serialized)
	{
		return $this->items = unserialize($serialized);
	}

	/**
	 * 通过键获取数据。
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * 为指定数据赋值。
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * 判断指定键的数据是否存在。
	 *
	 * @param string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		return $this->has($key);
	}

	/**
	 * 确定指定元素是否存在。
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		return !is_null(Arr::get($this->items, $key));
	}

	/**
	 * 通过键取消设置数据。
	 *
	 * @param string $key
	 */
	public function __unset($key)
	{
		$this->forget($key);
	}

	/**
	 * 从集合中移除项。
	 *
	 * @param string $key
	 */
	public function forget($key)
	{
		Arr::forget($this->items, $key);
	}

	/**
	 * 取消设置偏移量。
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset 要取消设置的偏移量。
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset($offset)
	{
		if ($this->offsetExists($offset)) {
			$this->forget($offset);
		}
	}

	/**
	 * 偏移量是否存在。
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset 要检查的偏移量。
	 * @return bool 成功返回 true，失败返回 false。
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	/**
	 * 检索偏移量。
	 * @see http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset 要检索的偏移量。
	 * @return mixed 可以返回所有值类型
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->offsetExists($offset) ? $this->get($offset) : null;
	}

	/**
	 * 设置偏移量。
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset 要分配值的偏移量。
	 * @param mixed $value 要设置的值。
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

}
