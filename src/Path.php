<?php

namespace Xin\Support;

final class Path
{
	/**
	 * 替换文件后缀
	 * @param string $file
	 * @param string $suffix
	 * @return string
	 */
	public static function replaceSuffix(string $file, string $suffix)
	{
		return self::basename($file) . "." . $suffix;
	}

	/**
	 * 获取文件路径的名字
	 * @param string $file
	 * @param bool $withSuffix
	 * @return string
	 */
	public static function basename(string $file, bool $withSuffix = false)
	{
		return basename($file, $withSuffix ? '' : '.' . self::suffix($file));
	}

	/**
	 * 获取文件的后缀名
	 * @param string $file
	 * @return string
	 */
	public static function suffix(string $file)
	{
		$dotIndex = strrpos($file, ".");
		if ($dotIndex === false) {
			return '';
		}

		return substr($file, $dotIndex + 1);
	}

	/**
	 * 拼接文件路径
	 * @param string|array ...$paths
	 * @return string
	 */
	public static function joins(...$paths)
	{
		$paths = array_filter($paths);
		$paths = array_map(function ($itemPaths) {
			return is_array($itemPaths) ? implode(DIRECTORY_SEPARATOR, array_filter($itemPaths)) : $itemPaths;
		}, $paths);
		return implode(DIRECTORY_SEPARATOR, $paths);
	}
}
