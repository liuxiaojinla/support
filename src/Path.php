<?php

namespace Xin\Support;

final class Path
{
	/**
	 * 获取文件路径的名字
	 * @param string $file
	 * @param bool $withSuffix
	 * @return string
	 */
	public static function basename(string $filepath, bool $withSuffix = false)
	{
		return basename($filepath, $withSuffix ? '' : '.' . self::suffix($filepath));
	}

	/**
	 * 替换文件路径的名字
	 * @param string $filepath
	 * @param string $newFilename
	 * @return string
	 */
	public static function replaceFilename(string $filepath, string $newFilename)
	{
		$dir = dirname($filepath);
		$suffix = self::suffix($filepath);

		if ($suffix !== '') {
			$newFilename .= '.' . $suffix;
		}

		return self::joins($dir, $newFilename);
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

	/**
	 * 拼接路径
	 * @param string $basePath
	 * @param string $subPath
	 * @return string
	 */
	public static function concat(string $basePath, string $subPath)
	{
		return self::joins(rtrim($basePath, '/'), ltrim($subPath, '/'));
	}
}
