<?php


namespace Xin\Support;

use FilesystemIterator;
use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\Filesystem as FlysystemFilesystem;
use League\Flysystem\PathNormalizer as FlysystemPathNormalizer;
use Psr\Http\Message\StreamInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Xin\Support\Security\Etag;

/**
 * 目录操作类
 */
final class File
{
	/**
	 * ETag 算法
	 */
	public const HASH_ETAG = 'etag';

	/**
	 * md5 算法
	 */
	public const HASH_MD5 = 'md5';

	/**
	 * Sha1 算法
	 */
	public const HASH_SHA1 = 'sha1';

	/**
	 * 获取文件hash
	 * @param SplFileInfo|string $file
	 * @param string|null $hashType
	 * @return string
	 */
	public static function hash($file, $hashType = null)
	{
		$hashType = $hashType ?: self::HASH_ETAG;
		$realPath = $file instanceof SplFileInfo ? $file->getRealPath() : $file;

		if (self::HASH_ETAG === $hashType) {
			return Etag::sum($realPath);
		}

		if (self::HASH_MD5 === $hashType) {
			return md5_file($realPath);
		}

		if (self::HASH_SHA1 === $hashType) {
			return sha1_file($realPath);
		}

		if (in_array($hashType, hash_algos(), true)) {
			return hash_file($hashType, $realPath, true);
		}

		throw new \RuntimeException("hash_type[{$hashType}] is not support.");
	}

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
	 * @param bool $recursive
	 * @return array
	 */
	public static function files(string $directory, bool $recursive = true)
	{
		$files = [];

		$each = function ($dir) use (&$each, &$files, $recursive) {
			$it = new \FilesystemIterator($dir);
			/**@var $file \SplFileInfo */
			foreach ($it as $file) {
				if ($file->isDir()) {
					if ($recursive) {
						$each($file->getPathname());
					}
				} else {
					$files[] = $file;
				}
			}
		};
		$each($directory);

		return $files;
	}

	/**
	 * 寻找与模式匹配的文件路径
	 * @param string $glob
	 * @param int $flags
	 * @return array|false
	 */
	public static function glob(string $glob, int $flags = 0)
	{
		return glob($glob, $flags);
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
	 * @param int $flags
	 * @param resource|null $context
	 * @return false|int
	 */
	public static function put(string $path, $data, int $flags = 0, $context = null)
	{
		$directory = dirname($path);
		self::mkdirOrExists($directory);

		if ($data instanceof StreamInterface) {
			$data = $data->getContents();
		} elseif (is_object($data) || is_array($data)) {
			$data = Json::encode($data);
		}

		return file_put_contents($path, $data, $flags, $context);
	}

	/**
	 * 追加写入文件
	 * @param string $path
	 * @param mixed $data
	 * @param mixed|null $context
	 * @return false|int
	 */
	public static function append(string $path, $data, $context = null)
	{
		return self::put($path, $data, FILE_APPEND, $context);
	}

	/**
	 * 读取文件
	 * @param string $path
	 * @param bool $useIncludePath
	 * @param resource|null $context
	 * @param int $offset
	 * @param int|null $length
	 * @return false|string
	 */
	public static function get(string $path, bool $useIncludePath = false, $context = null, int $offset = 0, int $length = null)
	{
		return file_get_contents($path, $useIncludePath, $context, $offset, $length);
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

	/**
	 * 获取文件扩展
	 * @return string
	 */
	public static function extension(SplFileInfo $file)
	{
		$extension = $file->getExtension();
		if (empty($extension)) {
			$mime = self::mime($file);
			$mime = explode("/", $mime);
			if (empty($mime)) {
				return '';
			}

			return strtolower(end($mime));
		}

		return strtolower($extension);
	}

	/**
	 * 获取mime类型
	 * @return string
	 */
	public static function mime(SplFileInfo $file)
	{
		return mime_content_type($file->getPathname());
	}
}
