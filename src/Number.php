<?php


namespace Xin\Support;

/**
 * 数字工具类
 */
final class Number
{

	/**
	 * 保留小数点两位
	 *
	 * @param float $n 要格式化的浮点数
	 * @param int $y 要保留的小说点位数
	 * @return float
	 */
	public static function formatFloat($n, $y = 2)
	{ // 保留小数点两位
		$str = "%." . ($y * 2) . "f";

		return floatval(substr(sprintf($str, $n), 0, -2));
	}

	/**
	 * 保留小数点两位
	 *
	 * @param float $n 要格式化的浮点数
	 * @param int $y 要保留的小说点位数
	 * @return float
	 */
	public static function formatFloat2($n, $y = 2)
	{
		return round($n, $y, PHP_ROUND_HALF_DOWN);
	}

	/**
	 * 格式化字节大小
	 *
	 * @param int $size 字节数
	 * @param string $delimiter 数字和单位分隔符
	 * @return string            格式化后的带单位的大小
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public static function formatBytes($size, $delimiter = '')
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		for ($i = 0; $size >= 1024 && $i < 5; $i++) {
			$size /= 1024;
		}

		return round($size, 2) . $delimiter . $units[$i];
	}

	/**
	 * 人性化数字
	 *
	 * @param int $num
	 * @return string
	 */
	public static function formatSimple($num)
	{
		if ($num < 1000) {
			return $num;
		}

		if ($num < 10000) {
			return round($num / 1000, 2) . "千";
		}

		if ($num < 100000000) {
			return round($num / 10000, 2) . "万";
		}

		return round($num / 100000000, 2) . "亿";
	}

}
