<?php

namespace Xin\Support;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

/**
 * 转换单数到复数形式
 */
final class Pluralizer
{

	/**
	 * 无数的词形。
	 *
	 * @var array
	 */
	public static $uncountable = [
		'audio',
		'bison',
		'cattle',
		'chassis',
		'compensation',
		'coreopsis',
		'data',
		'deer',
		'education',
		'emoji',
		'equipment',
		'evidence',
		'feedback',
		'firmware',
		'fish',
		'furniture',
		'gold',
		'hardware',
		'information',
		'jedi',
		'kin',
		'knowledge',
		'love',
		'metadata',
		'money',
		'moose',
		'news',
		'nutrition',
		'offspring',
		'plankton',
		'pokemon',
		'police',
		'rain',
		'recommended',
		'related',
		'rice',
		'series',
		'sheep',
		'software',
		'species',
		'swine',
		'traffic',
		'wheat',
	];

	/**
	 * @var Inflector
	 */
	protected static $inflector = null;

	/**
	 * 获取英语单词的复数形式。
	 *
	 * @param string $value
	 * @param int $count
	 * @return string
	 */
	public static function plural($value, $count = 2)
	{
		if ((int)abs($count) === 1 || self::uncountable($value)) {
			return $value;
		}

		$plural = self::inflector()->pluralize($value);

		return self::matchCase($plural, $value);
	}

	/**
	 * 确定给定值是否不可数。
	 *
	 * @param string $value
	 * @return bool
	 */
	protected static function uncountable($value)
	{
		return in_array(strtolower($value), self::$uncountable);
	}

	/**
	 * @return Inflector
	 */
	public static function inflector()
	{
		if (self::$inflector === null) {
			self::$inflector = InflectorFactory::create()->build();
		}

		return self::$inflector;
	}

	/**
	 * 尝试匹配两个字符串上的大小写。
	 *
	 * @param string $value
	 * @param string $comparison
	 * @return string
	 */
	protected static function matchCase($value, $comparison)
	{
		$functions = ['mb_strtolower', 'mb_strtoupper', 'ucfirst', 'ucwords'];

		foreach ($functions as $function) {
			if (call_user_func($function, $comparison) === $comparison) {
				return call_user_func($function, $value);
			}
		}

		return $value;
	}

	/**
	 * 获取英语单词的单数形式。
	 *
	 * @param string $value
	 * @return string
	 */
	public static function singular($value)
	{
		$singular = self::inflector()->singularize($value);

		return self::matchCase($singular, $value);
	}

}
