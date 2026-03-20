<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\Support;

use MyCLabs\Enum\Enum as BaseEnum;

abstract class Enum extends BaseEnum
{
	/**
	 * 字段标签映射
	 * @var array<array-key, string>
	 */
	protected static $labels = [];

	/**
	 * 获取字段标签
	 *
	 * @param array-key $value
	 * @param string|null $default
	 * @return string
	 */
	public static function label($value, ?string $default = '--')
	{
		return static::$labels[$value] ?? $default;
	}

	/**
	 * 获取所有字段标签
	 * @return array<array-key, string>
	 */
	public static function labels()
	{
		return static::$labels ?: static::$TEXT_MAP;
	}

	/**
	 * 获取枚举类型数据
	 * @return array<array<array-key,mixed>>
	 */
	public static function items()
	{
		return array_map(function ($value, $key) {
			return static::mapItem($value, $key);
		}, static::toArray(), array_keys(static::toArray()));
	}

	/**
	 * 获取字段值
	 * @return array
	 */
	protected static function mapItem($value, string $key)
	{
		return static::format($value);
	}

	/**
	 * 获取字段值
	 *
	 * @param mixed $value
	 * @return array
	 */
	public static function format($value)
	{
		return [
			'label' => static::label($value),
			'value' => $value,
		];
	}

	/**
	 * 获取所有字段值
	 * @return array
	 */
	public static function all()
	{
		return array_values(self::toArray());
	}

	/**
	 * 获取文本字段
	 *
	 * @param mixed $value
	 * @param string|null $default
	 * @return string
	 * @deprecated
	 */
	public static function text($value, ?string $default = '--')
	{
		return static::label($value, $default);
	}

	/**
	 * 获取文本字段
	 *
	 * @return string[]
	 * @deprecated
	 */
	public static function texts()
	{
		return static::labels();
	}

	/**
	 * 获取枚举类型数据
	 * @return array
	 * @deprecated
	 */
	public static function data()
	{
		return static::items();
	}
}
