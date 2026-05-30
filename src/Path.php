<?php

namespace Xin\Support;

final class Path
{
	/**
	 * 获取文件路径的根目录或向上查找 $levels 次目录部分
	 * @param string $filepath
	 * @param int $levels
	 * @return string
	 */
	public static function basepath(string $filepath, int $levels = 1)
	{
		if ($levels < 1) {
			return $filepath;
		}

		// 标准化分隔符以处理混合斜杠（如有必要），尽管 PHP 通常能处理它们，我们需要向上查找 $levels 次目录部分
		$currentPath = rtrim($filepath, '/\\');
		for ($i = 0; $i < $levels; $i++) {
			$unixPos = strrpos($currentPath, '/');
			$winPos = strrpos($currentPath, '\\');
			// 取两者中较大的那个作为"最后一个分隔符"的位置
			$lastSeparator = max($unixPos, $winPos);
			// 没有更多目录可以向上查找，则返回空
			if ($lastSeparator === false) {
				return '';
			}

			$currentPath = substr($currentPath, 0, $lastSeparator);
			// 处理路径变为空的情况（例如，文件在根目录）
			if ($currentPath === '') {
				return '';
			}
		}

		return $currentPath;
	}

	/**
	 * 获取文件名
	 * @param string $filepath
	 * @param bool $withExtension
	 * @return string
	 */
	public static function basename(string $filepath, bool $withExtension = false)
	{
		return basename($filepath, $withExtension ? '' : '.' . self::extension($filepath));
	}

	/**
	 * 获取文件名 - 别名
	 * @param string $filepath
	 * @param bool $withExtension
	 * @return string
	 */
	public static function filename(string $filepath, bool $withExtension = false)
	{
		return self::basename($filepath, $withExtension);
	}

	/**
	 * 替换文件路径的名字
	 * @param string $filepath
	 * @param string $newFilename
	 * @return string
	 */
	public static function replaceFilename(string $filepath, string $newFilename)
	{
		$basepath = self::basepath($filepath);
		$suffix = self::extension($filepath);

		if ($suffix !== '') {
			$newFilename .= '.' . $suffix;
		}

		return self::join($basepath, $newFilename);
	}

	/**
	 * 替换文件路径
	 * @param string $filepath
	 * @param string $newPath
	 * @return string
	 */
	public static function replacePath(string $filepath, string $newPath)
	{
		$filename = self::filename($filepath, true);

		return self::join($newPath, $filename);
	}

	/**
	 * 替换文件后缀
	 * @param string $filepath
	 * @param string $newExtension
	 * @return string
	 */
	public static function replaceExtension(string $filepath, string $newExtension)
	{
		$newFilename = self::basename($filepath, false) . "." . $newExtension;

		return self::join(self::basepath($filepath), $newFilename);
	}

	/**
	 * 获取文件的后缀名
	 * @param string $filepath
	 * @return string
	 */
	public static function extension(string $filepath)
	{
		$dotIndex = strrpos($filepath, ".");
		if ($dotIndex === false) {
			return '';
		}

		return substr($filepath, $dotIndex + 1);
	}

	/**
	 * 拼接文件路径
	 * @param string|array ...$paths
	 * @return string
	 */
	public static function join(...$paths)
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
		return self::join(rtrim($basePath, '/'), ltrim($subPath, '/'));
	}
}
