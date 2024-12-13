<?php

namespace Xin\Support;

/**
 * 进制转化器
 */
class Radix
{

	/**
	 * @var static
	 */
	protected static $instance62 = null;
	/**
	 * @var array
	 */
	protected $chars = [];
	/**
	 * @var array
	 */
	protected $chars2 = [];
	/**
	 * @var int
	 */
	protected $radix = 0;

	/**
	 * IDGenerator constructor.
	 *
	 * @param string $sequence
	 */
	public function __construct($sequence)
	{
		$this->chars = str_split($sequence);
		$this->chars2 = array_flip($this->chars);
		$this->radix = strlen($sequence);
	}

	/**
	 * 使用 62 进制转化器
	 *
	 * @return $this|null
	 */
	public static function radix62()
	{
		if (static::$instance62 === null) {
			static::$instance62 = new static(
				'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
			);
		}

		return static::$instance62;
	}

	/**
	 * 数字转字符串
	 *
	 * @param int $num
	 * @return string
	 */
	public function generate($num)
	{
		if (is_nan($num)) {
			return '';
		}

		$value = +$num;
		$result = [];

		do {
			$mod = abs($value % $this->radix);
			$value = ($value - $mod) / $this->radix;

			if ($value < $this->radix) {
				$value = intval($value);
			}

			array_unshift($result, $this->chars[$mod]);
		} while ($value);

		return implode('', $result);
	}

	/**
	 * 字符串转数字
	 *
	 * @param string $str
	 * @return int
	 */
	public function parse($str)
	{
		$strLastIndex = strlen($str) - 1;

		$pow = 0;
		$result = 0;
		do {
			$char = $str[$strLastIndex];
			if (!isset($this->chars2[$char])) {
				return 0;
			}

			$value = $this->chars2[$char];
			$result += $value * pow($this->radix, $pow);
			$pow++;
		} while (--$strLastIndex >= 0);

		return $result;
	}

}
