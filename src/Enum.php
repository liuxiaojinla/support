<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\Support;

use MyCLabs\Enum\Enum as BaseEnum;

class Enum extends BaseEnum
{

	/**
	 * @var string[]
	 */
	protected static $TEXT_MAP = [];

	/**
	 * 获取文本字段
	 *
	 * @param int $value
	 * @return string
	 */
	public static function text($value, $default = '--')
	{
		return static::$TEXT_MAP[$value] ?? $default;
	}

	/**
	 * 获取文本字段
	 *
	 * @return string[]
	 */
	public static function texts()
	{
		return static::$TEXT_MAP;
	}

	/**
	 * 获取枚举类型数据
	 * @return array
	 */
	public static function data()
	{
		$objClass = new \ReflectionClass(static::class);
		$constants = $objClass->getConstants();

		$result = [];
		foreach ($constants as $val) {
			$result[] = [
				'name'  => static::$TEXT_MAP[$val],
				'value' => $val,
			];
		}

		return $result;
	}

}
