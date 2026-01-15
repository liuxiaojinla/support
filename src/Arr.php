<?php


namespace Xin\Support;

use ArrayAccess;
use InvalidArgumentException;

/**
 * 数组工具类
 */
final class Arr
{

	/**
	 * 如果元素不存在，则使用“点”表示法将其添加到数组中
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	public static function add($array, $key, $value)
	{
		if (is_null(self::get($array, $key))) {
			self::set($array, $key, $value);
		}

		return $array;
	}

	/**
	 * 支持使用“点”表示法从数组中获取项
	 *
	 * @param ArrayAccess|array $array
	 * @param string|int $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get($array, $key, $default = null)
	{
		if (!self::accessible($array)) {
			return value($default);
		}

		if (is_null($key)) {
			return $array;
		}

		if (self::exists($array, $key)) {
			return $array[$key];
		}

		if (strpos($key, '.') === false) {
			return isset($array[$key]) ? $array[$key] : value($default);
		}

		foreach (explode('.', $key) as $segment) {
			if (self::accessible($array) && self::exists($array, $segment)) {
				$array = $array[$segment];
			} else {
				return value($default);
			}
		}

		return $array;
	}

	/**
	 * 给定值是否可由数组访问
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public static function accessible($value)
	{
		return is_array($value) || $value instanceof ArrayAccess;
	}

	/**
	 * 确定给定的键是否存在于提供的数组中
	 *
	 * @param ArrayAccess|array $array
	 * @param string|int $key
	 * @return bool
	 */
	public static function exists($array, $key)
	{
		if ($array instanceof ArrayAccess) {
			return $array->offsetExists($key);
		}

		return array_key_exists($key, $array);
	}

	/**
	 * 支持使用“点”表示法将数组项设置为给定值
	 * 如果没有给方法指定键，整个数组将被替换
	 *
	 * @param iterable|array $array
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	public static function set(&$array, $key, $value)
	{
		if (is_null($key)) {
			return $array = $value;
		}

		$keys = explode('.', $key);

		while (count($keys) > 1) {
			$key = array_shift($keys);

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if (!isset($array[$key]) || !is_array($array[$key])) {
				$array[$key] = [];
			}

			$array = &$array[$key];
		}

		$key = array_shift($keys);
		if (isset($array[$key]) && is_array($array[$key]) && is_array($value)) {
			$array[$key] = array_merge($array[$key], $value);
		} else {
			$array[$key] = $value;
		}

		return $array;
	}

	/**
	 * 支持使用“点”表示法检查数组中是否存在一个或多个项
	 *
	 * @param iterable|array $array
	 * @param string|array $keys
	 * @return bool
	 */
	public static function has($array, $keys)
	{
		$keys = (array)$keys;

		if (!$array || $keys === []) {
			return false;
		}

		foreach ($keys as $key) {
			$subKeyArray = $array;

			if (self::exists($array, $key)) {
				continue;
			}

			foreach (explode('.', $key) as $segment) {
				if (self::accessible($subKeyArray) && self::exists($subKeyArray, $segment)) {
					$subKeyArray = $subKeyArray[$segment];
				} else {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * 获取除指定的键数组以外的所有给定数组
	 *
	 * @param iterable|array $array
	 * @param array|string $keys
	 * @return iterable|array
	 */
	public static function except($array, $keys)
	{
		self::forget($array, $keys);

		return $array;
	}

	/**
	 * 使用“点”表示法从给定数组中删除一个或多个数组项
	 *
	 * @param iterable|array &$array
	 * @param array|string $keys
	 * @return void
	 */
	public static function forget(&$array, $keys)
	{
		$original = &$array;

		$keys = (array)$keys;

		if (count($keys) === 0) {
			return;
		}

		foreach ($keys as $key) {
			// if the exact key exists in the top-level, remove it
			if (self::exists($array, $key)) {
				unset($array[$key]);

				continue;
			}

			$parts = explode('.', $key);

			// clean up before each pass
			$array = &$original;

			while (count($parts) > 1) {
				$part = array_shift($parts);

				if (isset($array[$part]) && is_array($array[$part])) {
					$array = &$array[$part];
				} else {
					continue 2;
				}
			}

			unset($array[array_shift($parts)]);
		}
	}

	/**
	 * 如果给定的值不是数组且不是null，将其包装在一个数组中
	 *
	 * @param mixed $value
	 * @return array
	 */
	public static function wrap($value)
	{
		if (is_null($value)) {
			return [];
		}

		return is_array($value) ? $value : [$value];
	}

	/**
	 * 将数组使用点展平多维关联数组
	 *
	 * @param array $array
	 * @param string $prepend
	 * @return array
	 */
	public static function dot($array, $prepend = '')
	{
		$results = [];

		foreach ($array as $key => $value) {
			if (is_array($value) && !empty($value)) {
				$results = array_merge($results, self::dot($value, $prepend . $key . '.'));
			} else {
				$results[$prepend . $key] = $value;
			}
		}

		return $results;
	}

	/**
	 * 交叉连接给定的数组，返回所有可能的排列
	 * 使用用例：产品规格
	 *
	 * @param array ...$arrays
	 * @return array
	 */
	public static function crossJoin(...$arrays)
	{
		$results = [[]];

		foreach ($arrays as $index => $array) {
			$append = [];

			foreach ($results as $product) {
				foreach ($array as $item) {
					$product[$index] = $item;

					$append[] = $product;
				}
			}

			$results = $append;
		}

		return $results;
	}

	/**
	 * 把一个数组分成两个数组。一个带有键，另一个带有值
	 *
	 * @param array $array
	 * @return array
	 */
	public static function divide(array $array)
	{
		return [array_keys($array), array_values($array)];
	}

	/**
	 * 创建一个由键和值组成的数组，类似 Python 中 zip() 的内置函数，
	 * 用于将多个可迭代对象（如列表、元组等）“打包”成一个个元组
	 *
	 * @param mixed ...$arrays
	 * @return array
	 */
	public static function zip(...$arrays)
	{
		$result = [];
		$minLength = min(array_map('count', $arrays));
		for ($i = 0; $i < $minLength; $i++) {
			$tuple = [];
			foreach ($arrays as $arr) {
				$tuple[] = $arr[$i];
			}
			$result[] = $tuple;
		}
		return $result;
	}

	/**
	 * 从数组里面获取指定的数据
	 *
	 * @param array $data
	 * @param array $keys
	 * @return array
	 */
	public static function only($data, array $keys)
	{
		return array_intersect_key($data, array_flip($keys));
	}

	/**
	 * 从数组里面获取指定的数据，如果指定的key不存在，则赋值为默认值
	 *
	 * @param array $data
	 * @param array $keys
	 * @param mixed $default
	 * @return array
	 */
	public static function onlyWithDefault($data, array $keys, $default = null)
	{
		$result = [];

		foreach ($keys as $key) {
			if (isset($data[$key])) {
				$result[$key] = $data[$key];
			} elseif (strpos($key, '.')) {
				$result = self::get($data, $key);
			} else {
				$result[$key] = $default;
			}
		}

		return $result;
	}

	/**
	 * 返回数组中通过给定真值测试的最后一个元素
	 *
	 * @param array $array
	 * @param callable|null $callback
	 * @param mixed $default
	 * @return mixed
	 */
	public static function last($array, callable $callback = null, $default = null)
	{
		if (is_null($callback)) {
			return empty($array) ? value($default) : end($array);
		}

		return self::first(array_reverse($array, true), $callback, $default);
	}

	/**
	 * 返回数组中通过给定真值测试的第一个元素
	 *
	 * @param array|iterable $array
	 * @param callable|null $callback
	 * @param mixed $default
	 * @return mixed
	 */
	public static function first($array, callable $callback = null, $default = null)
	{
		if (is_null($callback)) {
			if (empty($array)) {
				return value($default);
			}

			foreach ($array as $item) {
				return $item;
			}
		}

		foreach ($array as $key => $value) {
			if (call_user_func($callback, $value, $key)) {
				return $value;
			}
		}

		return value($default);
	}

	/**
	 * 将多维数组展平为单个级别
	 *
	 * @param array $array
	 * @param int $depth
	 * @return array
	 */
	public static function flatten($array, $depth = INF)
	{
		$result = [];

		foreach ($array as $item) {
			if (!is_array($item)) {
				$result[] = $item;
			} else {
				$values = $depth === 1 ? array_values($item) : self::flatten($item, $depth - 1);

				foreach ($values as $value) {
					$result[] = $value;
				}
			}
		}

		return $result;
	}

	/**
	 * 检测数组所有元素是否都符合指定条件
	 *
	 * @param array|iterable $array
	 * @param string|callable $callback
	 * @return bool
	 */
	public static function every($array, $callback)
	{
		foreach ($array as $k => $v) {
			if (!$callback($v, $k)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 将项目推送到数组的开头
	 *
	 * @param array $array
	 * @param mixed $value
	 * @param mixed $key
	 * @return array
	 */
	public static function prepend($array, $value, $key = null)
	{
		if (is_null($key)) {
			array_unshift($array, $value);
		} else {
			$array = [$key => $value] + $array;
		}

		return $array;
	}

	/**
	 * 从数组中获取一个值，并将其移除
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function pull(&$array, $key, $default = null)
	{
		$value = self::get($array, $key, $default);

		self::forget($array, $key);

		return $value;
	}

	/**
	 * 从数组中获取一个或指定数量的随机值
	 *
	 * @param array $array
	 * @param int|null $number
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public static function random($array, $number = null)
	{
		$requested = is_null($number) ? 1 : $number;

		$count = count($array);

		if ($requested > $count) {
			throw new InvalidArgumentException(
				"You requested {$requested} items, but there are only {$count} items available."
			);
		}

		if (is_null($number)) {
			return $array[array_rand($array)];
		}

		if ((int)$number === 0) {
			return [];
		}

		$keys = array_rand($array, $number);

		$results = [];

		foreach ((array)$keys as $key) {
			$results[] = $array[$key];
		}

		return $results;
	}

	/**
	 * 打乱给定数组并返回结果
	 *
	 * @param array $array
	 * @param int|null $seed
	 * @return array
	 */
	public static function shuffle($array, $seed = null)
	{
		if (is_null($seed)) {
			shuffle($array);
		} else {
			mt_srand($seed);
			shuffle($array);
			mt_srand();
		}

		return $array;
	}

	/**
	 * 根据字段规则判断给定的数组是否满足条件
	 *
	 * @param mixed $array
	 * @param array $condition
	 * @param bool $any
	 * @return bool
	 */
	public static function is($array, $condition, $any = false)
	{
		if (self::isAssoc($condition)) {
			$temp = [];
			foreach ($condition as $key => $value) {
				$temp[] = [$key, '=', $value];
			}
			$condition = $temp;
		}

		foreach ($condition as $item) {
			[$field, $operator, $value] = $item;

			if ($array[$field]) {
				$result = $array[$field];
			} elseif (strpos($field, '.')) {
				$result = self::get($array, $field);
			} else {
				$result = null;
			}

			switch (strtolower($operator)) {
				case '===':
					$flag = $result === $value;
					break;
				case '!==':
					$flag = $result !== $value;
					break;
				case '!=':
				case '<>':
					$flag = $result != $value;
					break;
				case '>':
					$flag = $result > $value;
					break;
				case '>=':
					$flag = $result >= $value;
					break;
				case '<':
					$flag = $result < $value;
					break;
				case '<=':
					$flag = $result <= $value;
					break;
				case 'like':
					$flag = is_string($result) && false !== strpos($result, $value);
					break;
				case 'not like':
					$flag = is_string($result) && false === strpos($result, $value);
					break;
				case 'in':
					$flag = is_scalar($result) && in_array($result, $value, true);
					break;
				case 'not in':
					$flag = is_scalar($result) && !in_array($result, $value, true);
					break;
				case 'between':
					[$min, $max] = is_string($value) ? explode(',', $value) : $value;
					$flag = is_scalar($result) && $result >= $min && $result <= $max;
					break;
				case 'not between':
					[$min, $max] = is_string($value) ? explode(',', $value) : $value;
					$flag = is_scalar($result) && $result > $max || $result < $min;
					break;
				case '==':
				case '=':
				default:
					$flag = $result == $value;
			}

			if ($any && $flag) {
				return true;
			} elseif (!$any && !$flag) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 筛选的项目。
	 * @param array $arrays
	 * @param array|callable $condition
	 * @param bool $any
	 * @return array
	 */
	public static function where($arrays, $condition, $any = false)
	{
		$isAssoc = self::isAssoc($condition);

		if (is_callable($condition)) {
			$result = array_filter($arrays, $condition, ARRAY_FILTER_USE_BOTH);
		} else {
			$result = [];
			foreach ($arrays as $key => $array) {
				if (self::is($array, $condition, $any)) {
					if ($isAssoc) {
						$result[$key] = $array;
					} else {
						$result[] = $array;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * 筛选值不为 null 的数据。
	 *
	 * @param array $arrays
	 * @return array
	 */
	public static function whereNotNull($arrays)
	{
		return self::where($arrays, function ($value) {
			return !is_null($value);
		});
	}

	/**
	 * 给定值是否为关联数组
	 *
	 * @param array $arr 数组
	 * @return bool
	 */
	public static function isAssoc($arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	/**
	 * 除去数组中的空值和和附加键名
	 *
	 * @param array $params 要去除的数组
	 * @param array $filter 要额外过滤的数据
	 * @return array
	 */
	public static function filter(&$params, $filter = ["sign", "sign_type"])
	{
		foreach ($params as $key => $val) {
			if ($val == "" || (is_array($val) && count($val) == 0)) {
				unset ($params [$key]);
			} else {
				$len = count($filter);
				for ($i = 0; $i < $len; $i++) {
					if ($key == $filter [$i]) {
						unset ($params [$key]);
						array_splice($filter, $i, 1);
						break;
					}
				}
			}
		}

		return $params;
	}

	/**
	 * 不区分大小写的in_array实现
	 *
	 * @param $value
	 * @param $array
	 * @return bool
	 */
	public static function in($value, $array)
	{
		return in_array(strtolower($value), array_map('strtolower', $array));
	}

	/**
	 * 对数组排序
	 *
	 * @param array $array
	 * @return array
	 */
	public static function sort(&$array)
	{
		if (self::isAssoc($array)) {
			ksort($array);
		} else {
			sort($array);
		}
		reset($array);

		return $array;
	}

	/**
	 * 按键和值对数组进行递归排序
	 *
	 * @param array $array
	 * @return array
	 */
	public static function sortRecursive($array)
	{
		foreach ($array as &$value) {
			if (is_array($value)) {
				$value = self::sortRecursive($value);
			}
		}

		if (self::isAssoc($array)) {
			ksort($array);
		} else {
			sort($array);
		}

		return $array;
	}

	/**
	 * 使用指定的键进行数组排序
	 *
	 * @param array $array
	 * @param string $sortKey
	 */
	public static function sortWithKey(array &$array, string $sortKey = 'sort')
	{
		usort($array, function ($it1, $it2) use ($sortKey) {
			$sort1 = $it1[$sortKey] ?? 0;
			$sort2 = $it2[$sortKey] ?? 0;

			return $sort1 == $sort2 ? 0 : ($sort1 > $sort2 ? 1 : -1);
		});
	}

	/**
	 * 在每个项目上运行关联映射。
	 *
	 * 回调应返回一个具有单个键/值对的关联数组。
	 *
	 * @template TKey
	 * @template TValue
	 * @template TMapWithKeysKey of array-key
	 * @template TMapWithKeysValue
	 *
	 * @param array<TKey, TValue> $array
	 * @param callable(TValue, TKey): array<TMapWithKeysKey, TMapWithKeysValue> $callback
	 * @return array
	 */
	public static function mapWithKeys(array $array, callable $callback)
	{
		$result = [];

		foreach ($array as $key => $value) {
			$assoc = $callback($value, $key);

			foreach ($assoc as $mapKey => $mapValue) {
				$result[$mapKey] = $mapValue;
			}
		}

		return $result;
	}

	/**
	 * 映射数组。
	 *
	 * 回调应返回一个值。
	 *
	 * @template TKey
	 * @template TValue
	 * @template TMapKey of array-key
	 * @template TMapValue
	 *
	 * @param array<TKey, TValue> $array
	 * @param callable(TValue, TKey): TMapValue $callback
	 * @return array<TMapKey, TMapValue>
	 */
	public static function map(array $array, callable $callback, bool $isIndexKey = false)
	{
		$result = [];

		foreach ($array as $key => $value) {
			if ($isIndexKey) {
				$result[] = $callback($value, $key);
			} else {
				$result[$key] = $callback($value, $key);
			}
		}

		return $result;
	}

	/**
	 * 从数组中提取指定的值数组
	 *
	 * @param array $array
	 * @param string $column
	 * @param string $indexKey
	 * @return array
	 */
	public static function column(array $array, $column, $indexKey = null)
	{
		$result = [];

		foreach ($array as $row) {
			$key = $value = null;
			$keySet = $valueSet = false;
			if ($indexKey !== null && array_key_exists($indexKey, $row)) {
				$keySet = true;
				$key = (string)$row[$indexKey];
			}
			if ($column === null) {
				$valueSet = true;
				$value = $row;
			} elseif (is_array($row) && array_key_exists($column, $row)) {
				$valueSet = true;
				$value = $row[$column];
			}
			if ($valueSet) {
				if ($keySet) {
					$result[$key] = $value;
				} else {
					$result[] = $value;
				}
			}
		}

		return $result;
	}

	/**
	 * 解包数组
	 *
	 * @param array $array
	 * @param string|array $keys
	 * @return array
	 */
	public static function uncombine(array $array, $keys = null)
	{
		$result = [];

		if ($keys) {
			$keys = is_array($keys) ? $keys : explode(',', $keys);
		} else {
			$keys = array_keys(current($array));
		}

		foreach ($keys as $index => $key) {
			$result[$index] = [];
		}

		foreach ($array as $item) {
			foreach ($keys as $index => $key) {
				$result[$index][] = isset($item[$key]) ? $item[$key] : null;
			}
		}

		return $result;
	}

	/**
	 * 数组去重 - 二维数组
	 *
	 * @param array $array
	 * @param string $key
	 * @return array
	 * @link https://www.php.net/manual/zh/function.array-unique.php#116302
	 */
	public static function uniqueMulti($array, $key)
	{
		$i = 0;
		$temp_array = [];
		$key_array = [];

		foreach ($array as $val) {
			if (!in_array($val[$key], $key_array)) {
				$key_array[$i] = $val[$key];
				$temp_array[$i] = $val;
			}
			$i++;
		}

		return $temp_array;
	}

	/**
	 * 无极限分类
	 *
	 * @param array $list 数据源
	 * @param callable|null $itemHandler 额外处理回调函数
	 * @param int $pid 父id
	 * @param array $options
	 * @return array
	 */
	public static function tree(array $list, callable $itemHandler = null, $pid = 0, array $options = [])
	{
		$options = array_merge([
			'id' => 'id', // 要检索的ID键名
			'parent' => 'pid', // 要检索的parent键名
			'child' => 'child', // 要存放的子结果集
			'with_unknown' => false, // 是否把未知的上级当成1级返回
		], $options);

		if (is_null($itemHandler)) {
			$itemHandler = function ($level, &$value) {
			};
		}

		$level = 0;
		$handler = function (array &$list, $pid) use (&$handler, &$level, &$itemHandler, &$options) {
			$level++;
			$idKey = $options['id'];
			$parentKey = $options['parent'];
			$childKey = $options['child'];

			$result = [];
			foreach ($list as $key => $value) {
				if ($value[$parentKey] == $pid) {
					unset ($list[$key]);

					$flag = $itemHandler($level, $value);

					$childList = $handler($list, $value[$idKey]);
					if (!empty($childList)) {
						$value[$childKey] = $childList;
					}

					if ($flag !== false) {
						$result[] = $value;
						reset($list);
					}
				}
			}
			$level--;

			return $result;
		};

		$result = $handler($list, $pid);

		// 是否把未知的上级当成1级返回
		if (!empty($list) && $options['with_unknown']) {
			$level = 1;
			foreach ($list as &$value) {
				$itemHandler($level, $value);
			}
			unset($value);

			$result = array_merge($result, array_values($list));
		}

		return $result;
	}

	/**
	 * 解除Tree结构数据
	 *
	 * @param array $list
	 * @param string $child
	 * @return array
	 */
	public static function treeToList($list, $child = 'child')
	{
		$handler = function ($list, $child) use (&$handler) {
			$result = [];
			foreach ($list as $key => &$val) {
				$result[] = &$val;
				unset($list[$key]);
				if (isset($val[$child])) {
					$result = array_merge($result, $handler($val[$child], $child));
					unset($val[$child]);
				}
			}
			unset($val);

			return $result;
		};

		return $handler($list, $child);
	}

	/**
	 * 遍历树形数据
	 *
	 * @param array $data
	 * @param callable $callback
	 * @param mixed $parent
	 * @param array $options
	 * @return array
	 */
	public static function treeEach(array $data, callable $callback, &$parent = null, array $options = [])
	{
		$options = array_merge([
			'child' => 'child', // 要存放的子结果集
		], $options);
		$childKey = $options['child'];

		$handler = function (&$data, &$parent) use (&$handler, $callback, $childKey) {
			foreach ($data as &$item) {
				call_user_func_array($callback, [&$item, &$parent]);
				if (isset($item[$childKey])) {
					$handler($item[$childKey], $item);
				}
			}
			unset($item);

			return $data;
		};

		return $handler($data, $parent);
	}

	/**
	 * 遍历过滤树形数据
	 *
	 * @param array $data
	 * @param callable $filter
	 * @param array $options
	 * @return array
	 */
	public static function treeFilter(array $data, callable $filter, array $options = [])
	{
		$options = array_merge([
			'child' => 'child', // 要存放的子结果集
		], $options);
		$childKey = $options['child'];

		$handler = function (&$data) use (&$handler, &$filter, &$childKey) {
			foreach ($data as $key => &$item) {
				if (call_user_func_array($filter, [$item]) === false) {
					unset($data[$key]);
				} elseif (isset($item[$childKey])) {
					$item[$childKey] = $handler($item[$childKey]);
				}
			}
			unset($item);

			return $data;
		};

		return $handler($data);
	}

	/**
	 * 转换指定数组里面的 key
	 *
	 * @param array $array
	 * @param array|callable $keyMappings
	 * @return array
	 */
	public static function transformKeys(array $array, $keyMappings)
	{
		if (is_callable($keyMappings)) {
			return self::transformKey($array, $keyMappings);
		} else {
			foreach ($keyMappings as $oldKey => $newKey) {
				if (!array_key_exists($oldKey, $array)) {
					continue;
				}

				if (is_callable($newKey)) {
					[$newKey, $value] = call_user_func($newKey, $array[$oldKey], $oldKey, $array);
					$array[$newKey] = $value;
				} else {
					$array[$newKey] = $array[$oldKey];
				}

				unset($array[$oldKey]);
			}

			return $array;
		}
	}

	/**
	 * 转换数组键名
	 *
	 * @param array $array 原始数组
	 * @param callable $callback 键名转换回调函数
	 * @param bool $recursive 是否递归处理多维数组
	 * @return array 键名转换后的数组
	 */
	public static function transformKey(array $array, callable $callback, bool $recursive = false)
	{
		$result = [];
		foreach ($array as $key => $value) {
			$newKey = call_user_func($callback, $key);

			if ($recursive && is_array($value)) {
				$result[$newKey] = self::transformKey($value, $callback, $recursive);
			} else {
				$result[$newKey] = $value;
			}
		}
		return $result;
	}

	/**
	 * 将键名下划线转驼峰命名
	 *
	 * @param array $array
	 * @param bool $recursive
	 * @return array
	 */
	public static function camelCaseKeys(array $array, bool $recursive = false)
	{
		return self::transformKey($array, function ($key) {
			return Str::camel($key);
		}, $recursive);
	}

	/**
	 * 将键名下划线转大驼峰命名
	 *
	 * @param array $array
	 * @param bool $recursive
	 * @return array
	 */
	public static function studlyCaseKeys(array $array, bool $recursive = false)
	{
		return self::transformKey($array, function ($key) {
			return Str::studly($key);
		}, $recursive);
	}

	/**
	 * 将键名驼峰转下划线命名
	 *
	 * @param array $array
	 * @param bool $recursive
	 * @return array
	 */
	public static function snakeCaseKeys(array $array, bool $recursive = false)
	{
		return self::transformKey($array, function ($key) {
			return Str::snake($key);
		}, $recursive);
	}

	/**
	 * 将键名转小写
	 *
	 * @param array $array
	 * @param bool $recursive
	 * @return array
	 */
	public static function lowerCaseKeys(array $array, bool $recursive = false)
	{
		return self::transformKey($array, function ($key) {
			return strtolower($key);
		}, $recursive);
	}

	/**
	 * 将键名转大写
	 *
	 * @param array $array
	 * @param bool $recursive
	 * @return array
	 */
	public static function upperCaseKeys(array $array, bool $recursive = false)
	{
		return self::transformKey($array, function ($key) {
			return strtoupper($key);
		});
	}

	/**
	 * 合并默认数据（要合并的数组只会保留$default中所包含的键名）
	 *
	 * @param array $default
	 * @param array $data
	 * @return array
	 * @link https://www.php.net/manual/zh/function.array-intersect-key.php#80227
	 */
	public static function mergeDefault($default, $data)
	{
		$intersect = array_intersect_key($data, $default); //Get data for which a default exists
		$diff = array_diff_key($default, $data); //Get defaults which are not present in data

		return $diff + $intersect; //Arrays have different keys, return the union of the two
	}

	/**
	 * 解析字符串为数组
	 *
	 * @param string $string
	 * @return array
	 */
	public static function parse($string)
	{
		$string = trim(trim($string), ",;\r\n");
		if (empty($string)) {
			return [];
		}

		$array = preg_split('/[,;\r\n]+/', $string);

		if (strpos($string, ':')) {
			$value = [];
			foreach ($array as $val) {
				$val = explode(':', $val);
				if (isset($val[1]) && $val[0] !== '') {
					$value[$val[0]] = $val[1];
				} else {
					$value[] = $val[0];
				}
			}
		} else {
			$value = $array;
		}

		return $value;
	}

	/**
	 * 数组解析为字符串
	 *
	 * @param array $array
	 * @return string
	 */
	public static function toString($array)
	{
		$result = '';

		if (self::isAssoc($array)) {
			foreach ($array as $key => $val) {
				$result .= "{$key}:$val\n";
			}
		} else {
			$result = implode("\n", $array);
		}

		return $result;
	}

	/**
	 * 第二个索引数组向第一个索引数组覆盖
	 * @param array $base
	 * @param array $overlay
	 * @param null $length
	 * @return array
	 */
	public static function overlay(array $base, array $overlay, $length = null)
	{
		$length = $length === null ? count($base) : $length;
		$out = [];
		for ($i = 0; $i < $length; $i++) {
			$out[$i] = isset($overlay[$i]) ? $overlay[$i] : (isset($base[$i]) ? $base[$i] : null);
		}
		return $out;
	}

	/**
	 * 混合两个数组，取最长的数组长度
	 * @param array $base
	 * @param array $overlay
	 * @return array
	 */
	public static function blend(array $base, array $overlay)
	{
		$len = max(count($base), count($overlay));
		return self::overlay($base, $overlay, $len);
	}

	/**
	 * 裁剪两个数组，取最短的数组长度
	 * @param array $base
	 * @param array $overlay
	 * @return array
	 */
	public static function crop(array $base, array $overlay)
	{
		$len = min(count($base), count($overlay));
		return self::overlay($base, $overlay, $len);
	}

}
