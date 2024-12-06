<?php

namespace Xin\Support;

final class SQL
{

	/**
	 * 优化搜索关键字
	 * @param string $keywords
	 * @return string[]
	 */
	public static function keywords($keywords)
	{
		$keywords = trim($keywords);
		$keywords = Str::rejectEmoji($keywords);

		if (empty($keywords)) {
			return [];
		}

		return keywords_build_sql($keywords);
	}

	/**
	 * 生成计算位置字段
	 * @param float $longitude
	 * @param float $latitude
	 * @param string $longitudeName
	 * @param string $latitudeName
	 * @param string $aliasName
	 * @return string
	 */
	public static function mysqlDistance(
		$longitude, $latitude,
		$longitudeName = 'longitude', $latitudeName = 'latitude',
		$aliasName = 'distance'
	)
	{
		$sql = "ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$latitude}*PI()/180-{$latitudeName}*PI()/180)/2),2)+COS({$latitude}*PI()/180)*COS({$latitudeName}*PI()/180)*POW(SIN(({$longitude}*PI()/180-{$longitudeName}*PI()/180)/2),2)))*1000)";
		if ($aliasName) {
			$sql .= " AS {$aliasName}";
		}

		return $sql;
	}

}
