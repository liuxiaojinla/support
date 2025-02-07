<?php


namespace Xin\Support;

use FilesystemIterator;
use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\Filesystem as FlysystemFilesystem;
use League\Flysystem\PathNormalizer as FlysystemPathNormalizer;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * 目录操作类
 */
final class File
{
	/**
	 * 获取指定目录下所有的文件，包括子目录下的文件
	 *
	 * @param string $directory
	 * @return array
	 * @deprecated
	 * @see self::files()
	 */
	public static function getFiles(string $directory)
	{
		return self::files($directory);
	}

	/**
	 * 获取指定目录下所有的文件，包括子目录下的文件
	 *
	 * @param string $directory
	 * @return array
	 */
	public static function files(string $directory)
	{
		$files = [];

		$each = function ($dir) use (&$each, &$files) {
			$it = new \FilesystemIterator($dir);
			/**@var $file \SplFileInfo */
			foreach ($it as $file) {
				if ($file->isDir()) {
					$each($file->getPathname());
				} else {
					$files[] = $file;
				}
			}
		};
		$each($directory);

		return $files;
	}

	/**
	 * 递归指定目录下所有的文件，包括子目录下的文件
	 *
	 * @param string $directory
	 * @param callable $callback
	 */
	public static function each(string $directory, callable $callback)
	{
		$each = function ($dir) use (&$each, $callback) {
			$it = new \FilesystemIterator($dir);

			/**@var $file \SplFileInfo */
			foreach ($it as $file) {
				if ($callback($file) === false) {
					return false;
				}

				if ($file->isDir()) {
					if ($each($file->getPathname()) === false) {
						return false;
					}
				}
			}

			return true;
		};

		$each($directory);
	}

	/**
	 * 删除文件或目录
	 *
	 * @param string $directory
	 * @return bool
	 */
	public static function delete(string $directory)
	{
		$iterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($files as $fileInfo) {
			$fileInfo->isDir() ? rmdir($fileInfo->getRealPath()) : unlink($fileInfo->getRealPath());
		}

		return rmdir($directory);

		//		$each = function ($dir) use (&$each) {
		//			if (!is_dir($dir)) {
		//				return true;
		//			}
		//
		//			$it = new \FilesystemIterator($dir);
		//			$flag = true;
		//			/**@var $file \SplFileInfo */
		//			foreach ($it as $file) {
		//				if ($file->isDir()) {
		//					if ($each($file->getPathname()) === true) {
		//						if (!@rmdir($file->getPathname()))
		//							$flag = false;
		//					} else {
		//						$flag = false;
		//					}
		//				} else {
		//					if (!@unlink($file->getPathname()))
		//						$flag = false;
		//				}
		//			}
		//
		//			return $flag;
		//		};
		//
		//		if ($each($dir) === true) {
		//			if (!is_dir($dir) || @rmdir($dir)) {
		//				return true;
		//			}
		//		}

		//        return false;
	}

	/**
	 * 基于数组创建目录和文件
	 *
	 * @param array $files
	 */
	public static function createDirOrFiles(array $files)
	{
		foreach ($files as $key => $value) {
			$deep = substr($value, -1);
			if ($deep == DIRECTORY_SEPARATOR) {
				@mkdir($value, 0777, true);
			} else {
				@file_put_contents($value, '');
			}
		}
	}

	/**
	 * 写入数据到临时文件中
	 *
	 * @param mixed $data
	 * @param string $prefix
	 * @return false|string
	 */
	public static function putTempFile($data, $prefix = '')
	{
		$filePath = self::tempFilePath($prefix);
		if ($filePath === false) {
			return false;
		}

		if (file_put_contents($filePath, $data) === false) {
			return false;
		}

		return $filePath;
	}

	/**
	 * 建立一个具有唯一文件名的文件
	 *
	 * @param string $prefix
	 * @return false|string
	 */
	public static function tempFilePath($prefix = '')
	{
		return tempnam(sys_get_temp_dir(), empty($prefix) ? uniqid() : $prefix);
	}

	/**
	 * 获取上传的真实路径
	 * @param string $path
	 * @param FlysystemFilesystem $filesystem
	 * @return string
	 * @throws \ReflectionException
	 */
	public static function filesystemRealpath(string $path, FlysystemFilesystem $filesystem)
	{
		/** @var FlysystemConfig $config */
		$config = Reflect::get($filesystem, 'config');
		/** @var FlysystemPathNormalizer $pathNormalizer */
		$pathNormalizer = Reflect::get($filesystem, 'pathNormalizer');

		$realPath = $config->get('root', $config->get('path', '')) . DIRECTORY_SEPARATOR . $path;

		return $pathNormalizer->normalizePath($realPath);
	}

	/**
	 * 写入文件
	 * @param string $path
	 * @param mixed $data
	 * @return false|int
	 */
	public static function put(string $path, $data)
	{
		$directory = dirname($path);
		self::mkdirOrExists($directory);

		if (is_object($data) || is_array($data)) {
			$data = JSON::encode($data);
		}

		return file_put_contents($path, $data);
	}

	/**
	 * 读取文件
	 * @param string $path
	 * @return false|string
	 */
	public static function get(string $path)
	{
		return file_get_contents($path);
	}

	/**
	 * 创建目录
	 * @param string $directory
	 * @param int $permissions
	 * @param bool $recursive
	 * @param mixed $context
	 * @return bool
	 */
	public static function mkdir(string $directory, int $permissions = 0777, bool $recursive = true, $context = null)
	{
		return mkdir($directory, $permissions, $recursive, $context);
	}

	/**
	 * 不存在则创建目录
	 * @param string $directory
	 * @param int $permissions
	 * @param bool $recursive
	 * @param mixed $context
	 * @return bool
	 */
	public static function mkdirOrExists(string $directory, int $permissions = 0777, bool $recursive = true, $context = null)
	{
		if (is_dir($directory)) {
			return true;
		}

		return self::mkdir($directory, $permissions, $recursive, $context);
	}

}
