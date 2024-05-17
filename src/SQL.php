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

}
